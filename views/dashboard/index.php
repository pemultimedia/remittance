<div class="w-full max-w-[1200px] mx-auto flex flex-col gap-6 py-8 px-4">
    
    <!-- Header & Filtri -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold text-[#111418] dark:text-white">Dashboard Pagamenti</h2>
            <p class="text-[#617589] dark:text-gray-400">Panoramica delle Remittance ricevute da Amazon.</p>
        </div>
        
        <!-- Form Filtro Date -->
        <form method="GET" action="/dashboard" class="flex items-center gap-2 bg-white dark:bg-[#1e2732] p-2 rounded-lg border border-[#dbe0e6] dark:border-gray-700 shadow-sm">
            <div class="flex flex-col">
                <label class="text-[10px] uppercase font-bold text-[#617589] px-1">Dal</label>
                <input type="date" name="start" value="<?= htmlspecialchars($startDate) ?>" class="border-0 bg-transparent p-1 text-sm font-medium focus:ring-0 text-[#111418] dark:text-white">
            </div>
            <div class="h-8 w-px bg-[#dbe0e6]"></div>
            <div class="flex flex-col">
                <label class="text-[10px] uppercase font-bold text-[#617589] px-1">Al</label>
                <input type="date" name="end" value="<?= htmlspecialchars($endDate) ?>" class="border-0 bg-transparent p-1 text-sm font-medium focus:ring-0 text-[#111418] dark:text-white">
            </div>
            <button type="submit" class="bg-primary hover:bg-blue-600 text-white rounded p-2 transition-colors">
                <span class="material-symbols-outlined text-[20px]">filter_list</span>
            </button>
        </form>
    </div>

    <!-- Lista Remittance -->
    <div class="bg-white dark:bg-[#1e2732] rounded-xl border border-[#dbe0e6] dark:border-gray-700 overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-[#232d38] border-b border-[#dbe0e6] dark:border-gray-700 text-xs uppercase text-[#617589] dark:text-gray-400 font-bold tracking-wider">
                    <th class="p-4">Data Pagamento</th>
                    <th class="p-4">Fornitore</th>
                    <th class="p-4">Oggetto / Rif.</th>
                    <th class="p-4 text-right">Totale</th>
                    <th class="p-4 text-right">Azioni</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#dbe0e6] dark:divide-gray-700">
                <?php if(empty($remittances)): ?>
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">Nessun pagamento trovato in questo intervallo.</td></tr>
                <?php else: ?>
                    <?php foreach($remittances as $r): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-[#2a3441] transition-colors group cursor-pointer" onclick="window.location='/remittance?id=<?= $r['id'] ?>'">
                        <td class="p-4 text-sm font-medium text-[#111418] dark:text-white">
                            <?= date('d M Y', strtotime($r['payment_date'])) ?>
                            <div class="text-xs text-[#617589] font-normal" title="Data ricezione email">
                                Email: <?= date('d/m H:i', strtotime($r['received_at'])) ?>
                            </div>
                        </td>
						<td class="p-4">
                            <a href="/supplier?id=<?= $r['supplier_id'] ?>" 
                               onclick="event.stopPropagation()" 
                               class="font-medium text-primary hover:underline z-10 relative">
                                <?= htmlspecialchars($r['supplier_name']) ?>
                            </a>
                            <div class="text-xs text-[#617589] code"><?= htmlspecialchars($r['amazon_supplier_site_name']) ?></div>
                        </td>
                        <td class="p-4 text-sm text-[#617589] dark:text-gray-300">
                            <?= htmlspecialchars(substr($r['subject'], 0, 50)) ?>...
                            <div class="text-xs mt-1"><span class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300"><?= $r['items_count'] ?> voci</span></div>
                        </td>
                        <td class="p-4 text-right font-bold text-[#111418] dark:text-white">
                            <?= number_format($r['total_amount'], 2, ',', '.') ?> <?= $r['currency'] ?>
                        </td>
                        <td class="p-4 text-right">
                            <span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-colors">chevron_right</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>