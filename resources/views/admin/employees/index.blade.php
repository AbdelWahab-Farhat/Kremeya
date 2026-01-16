@extends('layouts.admin')

@section('page-title', 'Employees')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900">All Employees</h2>
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Add Employee</button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-sm uppercase">
                    <th class="px-6 py-3 font-medium">Employee</th>
                    <th class="px-6 py-3 font-medium">Contact</th>
                    <th class="px-6 py-3 font-medium">Salary</th>
                    <th class="px-6 py-3 font-medium">Joined</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($employees as $employee)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs mr-3">
                                {{ substr($employee->user->name ?? 'E', 0, 2) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $employee->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $employee->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $employee->user->email ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ $employee->user->phone ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-900">
                        LYD {{ number_format($employee->salary, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $employee->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <button class="text-gray-400 hover:text-indigo-600 transition-colors">Edit</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        No employees found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($employees->hasPages())
    <div class="p-4 border-t border-gray-100">
        {{ $employees->links() }}
    </div>
    @endif
</div>
@endsection
