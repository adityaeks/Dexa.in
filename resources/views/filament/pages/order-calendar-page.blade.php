<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Kalender Order Berdasarkan Due Date
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Kalender ini menampilkan semua order berdasarkan tanggal jatuh tempo (due date).
                Warna event menunjukkan status pembayaran:
            </p>
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Lunas (Paid)</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-amber-500 rounded mr-2"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Sebagian (Partial)</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Belum Bayar (Unpaid)</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gray-500 rounded mr-2"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Status Tidak Diketahui</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
