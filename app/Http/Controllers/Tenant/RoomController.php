<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\RoomImage;
use App\Services\Media\MediaStorage;
use App\Support\InputRules;
use App\Support\TenantPlanFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Tenant {room} routes do not reliably run implicit/custom binding before the controller
     * in this app (domain + middleware stack). Resolve from the route parameter explicitly.
     */
    protected function roomFromRoute(Request $request): Room
    {
        $value = $request->route('room');

        if ($value instanceof Room) {
            return $value;
        }

        return Room::on('tenant')->findOrFail((int) $value);
    }

    protected function canTrackAvailability(Request $request): bool
    {
        return TenantPlanFeatures::hasRequestFeature($request, 'availability_tracking');
    }

    protected function getTenantPlan(Request $request): ?\App\Models\Plan
    {
        $tenant = $request->attributes->get('tenant');
        if ($tenant instanceof Tenant) {
            return $tenant->loadMissing('plan')->plan;
        }

        return null;
    }

    protected function canAddRoom(Request $request): bool
    {
        $plan = $this->getTenantPlan($request);
        if (
            ! $plan
            || $plan->hasUnlimitedRooms()
            || TenantPlanFeatures::hasPlanFeature($plan, 'unlimited_rooms')
        ) {
            return true;
        }

        return $plan->allowsRoomCount(Room::count() + 1);
    }

    /**
     * Laravel's max rule uses KiB; keep this at or below PHP's effective upload cap so users get a
     * clear size error instead of "failed to upload" when the file never reaches the app.
     */
    protected function roomImageMaxKilobytes(): int
    {
        $appCap = 5120;
        $phpBytes = UploadedFile::getMaxFilesize();
        $phpKb = (int) floor(((float) $phpBytes) / 1024);

        if ($phpKb <= 0) {
            return $appCap;
        }

        return min($appCap, $phpKb);
    }

    /**
     * @return array<string, string>
     */
    protected function roomImageValidationMessages(): array
    {
        $hint = __('Could not upload this image. Your PHP limits are :upload per file and :post per request. Try a smaller JPEG or PNG, or raise upload_max_filesize and post_max_size in php.ini.', [
            'upload' => ini_get('upload_max_filesize') ?: '?',
            'post' => ini_get('post_max_size') ?: '?',
        ]);

        return [
            'images.*.uploaded' => $hint,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function roomImageFieldRules(): array
    {
        $maxKb = $this->roomImageMaxKilobytes();

        return [
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpeg,jpg,png', 'max:'.$maxKb],
        ];
    }

    public function index(Request $request): View|RedirectResponse
    {
        $plan = $this->getTenantPlan($request);
        $canTrackAvailability = TenantPlanFeatures::hasPlanFeature($plan, 'availability_tracking');

        if (! $canTrackAvailability) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Availability tracking is available on Standard and Premium plans.');
        }

        $rooms = Room::orderBy('name')->get();
        $roomCount = $rooms->count();
        $atLimit = $plan && !$plan->hasUnlimitedRooms() && $roomCount >= $plan->max_rooms;
        $maxRooms = $plan?->max_rooms;
        $roomsForJs = $rooms->map(fn (Room $r) => [
            'id' => $r->id,
            'name' => $r->name,
            'description' => $r->description,
            'type' => $r->type,
            'capacity' => $r->capacity,
            'price_per_night' => $r->price_per_night,
            'is_available' => $r->is_available,
            // Same host as the dashboard (route() can point at APP_URL and break PATCH on tenant domains).
            'update_url' => tenant_url('rooms/'.$r->id),
        ])->values()->all();
        return view('Tenant.rooms.index', [
            'rooms' => $rooms,
            'atLimit' => $atLimit,
            'maxRooms' => $maxRooms,
            'roomsForJs' => $roomsForJs,
            'openModal' => session('openModal'),
            'editRoomId' => session('editRoomId'),
            'roomImageMaxKb' => $this->roomImageMaxKilobytes(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->canTrackAvailability($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Availability tracking is available on Standard and Premium plans.');
        }

        if (!$this->canAddRoom($request)) {
            $plan = $this->getTenantPlan($request);
            return redirect()
                ->route('tenant.rooms.index')
                ->with('error', 'Your plan allows up to ' . $plan->max_rooms . ' rooms. Upgrade to add more.');
        }
        return view('Tenant.rooms.create', [
            'roomImageMaxKb' => $this->roomImageMaxKilobytes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->canTrackAvailability($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Availability tracking is available on Standard and Premium plans.');
        }

        if (!$this->canAddRoom($request)) {
            $plan = $this->getTenantPlan($request);
            return redirect()
                ->route('tenant.rooms.index')
                ->with('error', 'Your plan allows up to ' . $plan->max_rooms . ' rooms. Upgrade to add more.');
        }

        try {
            $validated = $request->validate(array_merge([
                'name' => InputRules::title(255, true),
                'description' => ['nullable', 'string'],
                'type' => ['required', 'in:room,cottage'],
                'capacity' => ['nullable', 'integer', 'min:1'],
                'price_per_night' => InputRules::money(true, 0.0),
                'is_available' => ['boolean'],
            ], $this->roomImageFieldRules()), $this->roomImageValidationMessages());
        } catch (ValidationException $e) {
            return redirect()
                ->route('tenant.rooms.index')
                ->with('openModal', 'create')
                ->withErrors($e->errors())
                ->withInput();
        }

        $validated['is_available'] = $request->boolean('is_available');
        unset($validated['images']);

        $room = Room::create($validated);

        // Handle image uploads (multiple allowed). First image becomes primary thumbnail.
        if ($request->hasFile('images')) {
            $media = app(MediaStorage::class);
            $paths = [];
            foreach ($request->file('images') as $file) {
                if (!$file) {
                    continue;
                }
                $stored = $media->storeImage($file, 'room_images');
                $path = $stored['path'];
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $path,
                ]);
                $paths[] = $path;
            }
            if (!$room->image_path && count($paths) > 0) {
                $room->image_path = $paths[0];
                $room->save();
            }
        }
        if (class_exists(ActivityLog::class) && \Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('room.created', 'Room "' . $room->name . '" created.');
        }
        return redirect()
            ->route('tenant.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function edit(Request $request): View
    {
        if (! $this->canTrackAvailability($request)) {
            abort(403, 'Availability tracking is not enabled in your current subscription.');
        }

        $room = $this->roomFromRoute($request);

        return view('Tenant.rooms.edit', [
            'room' => $room,
            'roomImageMaxKb' => $this->roomImageMaxKilobytes(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! $this->canTrackAvailability($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Availability tracking is available on Standard and Premium plans.');
        }

        $room = $this->roomFromRoute($request);

        try {
            $validated = $request->validate(array_merge([
                'name' => InputRules::title(255, true),
                'description' => ['nullable', 'string'],
                'type' => ['required', 'in:room,cottage'],
                'capacity' => ['nullable', 'integer', 'min:1'],
                'price_per_night' => InputRules::money(true, 0.0),
                'is_available' => ['boolean'],
            ], $this->roomImageFieldRules()), $this->roomImageValidationMessages());
        } catch (ValidationException $e) {
            return redirect()
                ->route('tenant.rooms.index')
                ->with('openModal', 'edit')
                ->with('editRoomId', $room->id)
                ->withErrors($e->errors())
                ->withInput();
        }

        $validated['is_available'] = $request->boolean('is_available');
        unset($validated['images']);

        $room->update($validated);

        // Append any newly uploaded images; keep existing ones.
        if ($request->hasFile('images')) {
            $media = app(MediaStorage::class);
            $paths = [];
            foreach ($request->file('images') as $file) {
                if (!$file) {
                    continue;
                }
                $stored = $media->storeImage($file, 'room_images');
                $path = $stored['path'];
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $path,
                ]);
                $paths[] = $path;
            }
            // If room has no primary image yet, set first newly uploaded as thumbnail.
            if (!$room->image_path && count($paths) > 0) {
                $room->image_path = $paths[0];
                $room->save();
            }
        }
        if (class_exists(ActivityLog::class) && \Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('room.updated', 'Room "' . $room->name . '" updated.');
        }
        return redirect()
            ->route('tenant.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        if (! $this->canTrackAvailability($request)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('error', 'Availability tracking is available on Standard and Premium plans.');
        }

        $room = $this->roomFromRoute($request);

        $name = $room->name;
        $room->delete();
        if (class_exists(ActivityLog::class) && \Schema::connection('tenant')->hasTable('activity_logs')) {
            ActivityLog::log('room.deleted', 'Room "' . $name . '" deleted.');
        }
        return redirect()
            ->route('tenant.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
