@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Inventario')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ $message }}</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Total Items</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_items'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Elements</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['total_elements'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Kits</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['total_kits'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Components</h3>
            <p class="text-3xl font-bold text-orange-600">{{ $stats['total_components'] }}</p>
        </div>
    </div>

    <!-- Stock Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Low Stock Items</h3>
            @if(count($lowStockItems) > 0)
                <ul class="space-y-2">
                    @foreach($lowStockItems as $item)
                        <li class="flex justify-between items-center border-b pb-2">
                            <span>{{ $item['name'] }} ({{ $item['type'] }})</span>
                            <span class="text-red-600 font-semibold">{{ $item['current_stock'] }}/{{ $item['min_stock'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">No low stock items</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Over Stock Items</h3>
            @if(count($overStockItems) > 0)
                <ul class="space-y-2">
                    @foreach($overStockItems as $item)
                        <li class="flex justify-between items-center border-b pb-2">
                            <span>{{ $item['name'] }} ({{ $item['type'] }})</span>
                            <span class="text-yellow-600 font-semibold">{{ $item['current_stock'] }}/{{ $item['max_stock'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">No over stock items</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Categories</h3>
            @if(count($categoryDistribution) > 0)
                <ul class="space-y-2">
                    @foreach($categoryDistribution as $category)
                        <li class="flex justify-between items-center border-b pb-2">
                            <span>{{ $category->name }}</span>
                            <span class="text-blue-600 font-semibold">{{ $category->items_count }} items</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">No categories found</p>
            @endif
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Movements</h3>
            @if(count($recentMovements) > 0)
                <div class="space-y-3">
                    @foreach($recentMovements as $movement)
                        <div class="border-b pb-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-medium">{{ $movement['component']['name'] }}</span>
                                    <p class="text-sm text-gray-600">{{ $movement['type'] }}</p>
                                    <p class="text-sm text-gray-500">by {{ $movement['user']['name'] }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-semibold">{{ $movement['quantity'] }}</span>
                                    <p class="text-xs text-gray-500">{{ $movement['created_at']->format('M j, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No recent movements</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Top Value Components</h3>
            @if(count($topValueComponents) > 0)
                <div class="space-y-3">
                    @foreach($topValueComponents as $component)
                        <div class="flex justify-between items-center border-b pb-2">
                            <span>{{ $component->name }}</span>
                            <div class="text-right">
                                <span class="font-semibold">${{ number_format($component->total_value, 2) }}</span>
                                <p class="text-xs text-gray-500">{{ $component->current_stock }} × ${{ number_format($component->purchase_cost, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No components with value found</p>
            @endif
        </div>
    </div>

        <!-- Total Inventory Value -->
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Total Inventory Value</h3>
            <p class="text-4xl font-bold text-green-600">${{ number_format($stats['total_value'], 2) }}</p>
        </div>
    </div>
</div>
@endsection