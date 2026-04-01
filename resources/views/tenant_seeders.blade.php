<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants Information</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- Tailwind CSS CDN -->
</head>
<body class="bg-gray-50 font-sans">

<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Tenants Information</h1>

    @if($tenants->isEmpty())
        <p class="text-gray-500">No tenants found.</p>
    @else
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr class="text-center">
                        <th class="px-4 py-2 border">ID</th>
                        <th class="px-4 py-2 border">Tenant Name</th>
                        <th class="px-4 py-2 border">Database Name</th>
                        <th class="px-4 py-2 border">Email</th>
                        <th class="px-4 py-2 border">Primary hostname</th>
                        <th class="px-4 py-2 border">Created At</th>
                        <th class="px-4 py-2 border">Updated At</th>
                        <th class="px-4 py-2 border">Metadata</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-center">
                    @foreach($tenants as $tenant)
                        <tr>
                            <td class="px-4 py-2 border">{{ $tenant->id }}</td>
                            <td class="px-4 py-2 border">{{ $tenant->tenant_name }}</td>
                            <td class="px-4 py-2 border">{{ $tenant->database_name }}</td>
                            <td class="px-4 py-2 border">{{ $tenant->email }}</td>
                            <td class="px-4 py-2 border">{{ $tenant->domains->firstWhere('is_primary', true)?->domain ?? $tenant->domains->first()?->domain ?? '—' }}</td>
                            <td class="px-4 py-2 border">{{ $tenant->created_at }}</td>
                            <td class="px-4 py-2 border">{{ $tenant->updated_at }}</td>
                            <td class="px-4 py-2 border">{{ $tenant->metadata }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

</body>
</html>
