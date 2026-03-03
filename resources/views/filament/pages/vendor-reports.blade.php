<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    {{-- Period selector --}}
    <div class="flex items-center gap-3 mb-6">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Period:</span>
        <select wire:model.live="period"
            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-3 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="7days">Last 7 Days</option>
            <option value="30days">Last 30 Days</option>
            <option value="3months">Last 3 Months</option>
            <option value="6months">Last 6 Months</option>
            <option value="12months">Last 12 Months</option>
            <option value="all">All Time</option>
        </select>
    </div>

    {{-- Summary cards --}}
    @php $summary = $this->getSummary(); @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Orders</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['orders']) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Gross Order Value</p>
            <p class="text-2xl font-bold text-gray-600 dark:text-gray-300 mt-1">
                {{ number_format($summary['revenue'], 2) }} EGP</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Net Earnings (After Commission)
            </p>
            <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">
                {{ number_format($summary['earnings'], 2) }} EGP</p>
        </div>
    </div>

    {{-- Earnings chart --}}
    @php $chartData = $this->getChartData(); @endphp
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm mb-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Monthly Earnings</h3>
        <div wire:ignore x-data="{
            chart: null,
            init() {
                this.chart = new Chart($refs.canvas, {
                    type: 'bar',
                    data: {
                        labels: {{ json_encode($chartData['labels']) }},
                        datasets: [
                            { label: 'Net Earnings (EGP)', data: {{ json_encode($chartData['earnings']) }}, backgroundColor: 'rgba(34,197,94,0.7)', borderRadius: 4 }
                        ]
                    },
                    options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
                });
            }
        }"
            @update-vendor-chart.window="
                chart.data.labels = $event.detail.chartData.labels;
                chart.data.datasets[0].data = $event.detail.chartData.earnings;
                chart.update();
            ">
            <canvas x-ref="canvas" style="max-height:300px;"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Monthly breakdown --}}
        <div
            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Monthly Breakdown</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Month
                            </th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Orders
                            </th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Gross
                            </th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Net
                                Earned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($this->getMonthlyBreakdown() as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200">{{ $row['month'] }}
                                </td>
                                <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ $row['orders'] }}
                                </td>
                                <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-400">
                                    {{ number_format($row['revenue'], 2) }}</td>
                                <td class="px-4 py-2 text-right text-success-600 dark:text-success-400 font-semibold">
                                    {{ number_format($row['earnings'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Orders by status --}}
        <div
            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Orders by Status</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Status
                            </th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Count
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($this->getOrdersByStatus() as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-2">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match ($row['status']) {
                                        'completed', 'delivered' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'paid', 'processing' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'shipped' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                        'cancelled', 'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    } }}">
                                        {{ ucfirst($row['status']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $row['total'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-gray-400">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top products --}}
    <div
        class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Top 10 Products by Revenue</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">#</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Product
                        </th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Qty Sold
                        </th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Revenue
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($this->getTopProducts() as $i => $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-2 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200 max-w-[240px] truncate">
                                {{ $row['title'] }}</td>
                            <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ $row['qty'] }}</td>
                            <td class="px-4 py-2 text-right text-success-600 dark:text-success-400 font-medium">
                                {{ number_format($row['revenue'], 2) }} EGP</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">No data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
