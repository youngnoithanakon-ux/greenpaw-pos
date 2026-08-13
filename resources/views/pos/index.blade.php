<x-app-layout>
    <x-slot name="title">POS — GreenPaw</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-data="posSystem({{ $grandTotal }})">
        
        {{-- ===== ฝั่งสินค้า ===== --}}
        <div class="lg:col-span-8 xl:col-span-9 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🌱</span> หน้าจอขายสินค้า
                </h2>
            </div>
            
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                </div>
                <input type="text" x-model="searchQuery" class="block w-full pl-10 pr-3 py-3 border-2 border-green-500 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition sm:text-lg" placeholder="ค้นหาชื่อสินค้า...">
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($products as $p)
                @php $outOfStock = $p->stock_qty <= 0; @endphp
                <div x-show="matchesSearch('{{ addslashes($p->product_name) }}')" 
                     class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition-shadow {{ $outOfStock ? 'opacity-70 grayscale-[50%]' : '' }}">
                    
                    <div class="aspect-square bg-green-50 flex items-center justify-center overflow-hidden">
                        @if($p->image_path && file_exists(storage_path('app/public/products/'.$p->image_path)))
                            <img src="{{ asset('storage/products/'.$p->image_path) }}" alt="{{ $p->product_name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-5xl opacity-50">🌱</span>
                        @endif
                    </div>
                    
                    <div class="p-3 flex flex-col flex-grow text-center">
                        <strong class="text-sm text-gray-700 leading-tight mb-1 h-10 line-clamp-2">{{ $p->product_name }}</strong>
                        <div class="text-green-600 font-bold text-lg mb-1">฿{{ number_format($p->sale_price, 2) }}</div>
                        <div class="text-xs text-gray-500 mb-3">
                            @if($outOfStock)
                                <span class="text-red-500 font-semibold">🚫 สินค้าหมด</span>
                            @else
                                คงเหลือ: {{ $p->stock_qty }}
                            @endif
                        </div>
                        
                        <div class="mt-auto">
                            <form method="POST" action="{{ route('pos.add') }}" class="flex gap-2">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $p->id }}">
                                <input type="number" name="qty" value="{{ $outOfStock ? 0 : 1 }}"
                                    min="0.1" step="0.1" class="w-16 px-1 py-1.5 text-center border border-gray-300 rounded-lg text-sm focus:ring-green-500 focus:border-green-500"
                                    {{ $outOfStock ? 'disabled' : '' }}>
                                @if($outOfStock)
                                    <button type="button" class="flex-1 bg-gray-300 text-gray-500 rounded-lg text-sm font-semibold cursor-not-allowed py-1.5" disabled>หมด</button>
                                @else
                                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition py-1.5">+ ใส่</button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ===== ฝั่งตะกร้า ===== --}}
        <div class="lg:col-span-4 xl:col-span-3 bg-white rounded-2xl shadow-lg border border-gray-100 flex flex-col lg:sticky lg:top-20" style="max-height: calc(100vh - 100px);">
            <div class="p-4 border-b border-gray-100 bg-green-50/50 rounded-t-2xl">
                <div class="text-xs font-semibold text-green-800 uppercase tracking-wider mb-2">ข้อมูลผู้ขาย</div>
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-green-200 text-green-700 flex items-center justify-center font-bold">
                        {{ substr(auth()->user()->fullname, 0, 1) }}
                    </div>
                    <strong class="text-gray-700">{{ auth()->user()->fullname }}</strong>
                </div>
            </div>

            <div class="p-4 flex flex-col flex-grow overflow-hidden">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-3">
                    <span>🛒</span> รายการในตะกร้า
                </h3>

                <div class="flex-grow overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                    @forelse($cart as $id => $item)
                    @php $subtotal = $item['price'] * $item['qty']; @endphp
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                        <div class="flex-1 pr-2">
                            <div class="font-semibold text-gray-800 text-sm leading-snug mb-1">{{ $item['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $item['qty'] }} × ฿{{ number_format($item['price'], 2) }}</div>
                        </div>
                        <div class="text-right flex flex-col items-end justify-between h-full">
                            <div class="font-bold text-gray-800 text-sm mb-1">฿{{ number_format($subtotal, 2) }}</div>
                            <a href="{{ route('pos.remove', $id) }}" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded p-1 transition" title="ลบ">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-10">
                        <div class="text-5xl mb-2 opacity-50">🛒</div>
                        <p class="text-sm font-medium">ยังไม่มีสินค้าในตะกร้า</p>
                    </div>
                    @endforelse
                </div>
            </div>

            @if(!empty($cart))
            <div class="p-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <div class="flex justify-between items-end mb-4">
                    <span class="text-gray-500 font-medium text-sm">รวมทั้งสิ้น:</span>
                    <strong class="text-2xl text-green-600 leading-none">฿{{ number_format($grandTotal, 2) }}</strong>
                </div>

                {{-- เงินสดที่รับ + เงินทอน --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">💵 รับเงินมา (บาท)</label>
                    <input type="number" x-model.number="cash" min="0" step="1"
                           placeholder="0.00"
                           class="block w-full px-4 py-3 border-2 border-green-500 rounded-xl text-xl font-bold text-right outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-600 transition shadow-inner">
                    
                    <div x-show="cash > 0 && change >= 0" style="display: none;" class="mt-2 bg-green-100 border border-green-200 rounded-lg p-3 flex justify-between items-center shadow-sm">
                        <span class="text-sm font-semibold text-green-800">เงินทอน:</span>
                        <strong class="text-xl text-green-700" x-text="'฿' + formatNumber(change)"></strong>
                    </div>
                    
                    <div x-show="cash > 0 && change < 0" style="display: none;" class="mt-2 bg-red-100 border border-red-200 rounded-lg p-3 flex justify-between items-center shadow-sm">
                        <span class="text-sm font-semibold text-red-800">ยังขาดอีก:</span>
                        <strong class="text-xl text-red-700" x-text="'฿' + formatNumber(Math.abs(change))"></strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('pos.checkout') }}" id="checkoutForm" @submit.prevent="submitCheckout">
                    @csrf
                    <input type="hidden" name="received_amount" :value="cash">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition-colors flex items-center justify-center gap-2 text-lg">
                        <span>💰</span> ชำระเงิน / ปิดบิล
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posSystem', (total) => ({
                searchQuery: '',
                grandTotal: total,
                cash: null,
                
                get change() {
                    if (this.cash === null || this.cash === '') return 0;
                    return this.cash - this.grandTotal;
                },
                
                matchesSearch(name) {
                    if (this.searchQuery === '') return true;
                    return name.toLowerCase().includes(this.searchQuery.toLowerCase());
                },
                
                formatNumber(num) {
                    return Number(num).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2});
                },
                
                submitCheckout(e) {
                    const c = parseFloat(this.cash) || 0;
                    if (c > 0 && c < this.grandTotal) {
                        const short = this.formatNumber(this.grandTotal - c);
                        if (!confirm(`เงินยังไม่พอ ขาดอีก ฿${short}\nยืนยันปิดบิลเลยไหม?`)) return false;
                    } else if (c === 0) {
                        if (!confirm('ยืนยันการชำระเงินและปิดบิล?')) return false;
                    }
                    e.target.submit();
                }
            }));
        });
    </script>
</x-app-layout>
