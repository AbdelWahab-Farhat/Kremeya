@extends('layouts.admin')

@section('page-title', 'Customers')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900">All Customers</h2>
        {{-- <a href="{{ route('admin.customers.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Add Customer</a> --}}
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-sm uppercase">
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Phone</th>
                    <th class="px-6 py-3 font-medium">Location</th>
                    <th class="px-6 py-3 font-medium">Joined</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs mr-3">
                                {{ substr($customer->user->name ?? 'U', 0, 2) }}
                            </div>
                            <span class="font-medium text-gray-900">{{ $customer->user->name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 font-normal">{{ $customer->user->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $customer->user->phone ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $customer->city->name ?? '-' }}, {{ $customer->region->name ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-sm">
                        {{ $customer->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <button class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No customers found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div class="p-4 border-t border-gray-100">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection
