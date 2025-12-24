<div class="w-full max-w-[1200px] mx-auto flex flex-col gap-6 py-8 px-4">
    
    <div class="flex items-center gap-2 text-sm text-[#617589]">
        <a href="/dashboard" class="hover:text-primary flex items-center gap-1 text-decoration-none text-[#617589]">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Dashboard
        </a>
        <span>/</span>
        <span class="text-[#111418] font-medium">Fornitore</span>
    </div>

    <!-- Header Fornitore -->
    <div class="bg-[#101922] text-white rounded-xl p-8 shadow-md flex flex-col md:flex-row justify-between items-center gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="bg-white/10 p-2 rounded text-primary">
                    <span class="material-symbols-outlined text-[24px]">storefront</span>
                </div>
                <h1 class="text-3xl font-bold m-0"><?= htmlspecialchars($supplier['name']) ?></h1>
            </div>
            <div class="flex gap-4 text-sm text-gray-400">
                <span>Site: <code class="text-white"><?= htmlspecialchars($supplier['amazon_supplier_site_name']) ?></code></span>
                <span>Valuta: <span class="text-white"><?= htmlspecialchars($supplier['currency']) ?></span></span>
            </div>
        </div>
        
        <!-- Filtri Data per Fornitore -->
        <form method="GET" action="/supplier" class="flex items-center gap-2 bg-white/10 p-2 rounded-lg border border-white/10">
            <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
            <div class="flex flex-col">
                <label class="text-[10px] uppercase font-bold text-gray-400 px-1">Dal</label>
                <input type="date" name="start" value="<?= htmlspecialchars($startDate) ?>" class="border-0 bg-transparent p-1 text-sm font-medium focus:ring-0 text-white">
            </div>
            <div class="h-8 w-px bg-white/20"></div>
            <div class="flex flex-col">
                <label class="text-[10px] uppercase font-bold text-gray-400 px-1">Al</label>
                <input type="date" name="end" value="<?= htmlspecialchars($endDate) ?>" class="border-0 bg-transparent p-1 text-sm font-medium focus:ring-0 text-white">
            </div>
            <button type="submit" class="bg-primary hover:bg-blue-500 text-white rounded p-2 transition-colors">
                <span class="material-symbols-outlined text-[20px]">refresh</span>
            </button>
        </form>
    </div>

    <!-- Lista Documenti -->
    <div class="bg-white dark:bg-[#1e2732] rounded-xl border border-[#dbe0e6] dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-[#dbe0e6] dark:border-gray-700 font-bold text-[#111418] dark:text-white flex justify-between items-center">
            <span>Documenti (<?= count($documents) ?>)</span>
            <span class="text-xs font-normal text-[#617589]">Ultimi 90 giorni (default)</span>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-[#232d38] border-b border-[#dbe0e6] dark:border-gray-700 text-xs uppercase text-[#617589] dark:text-gray-400 font-bold">
                    <th class="p-4">Numero Documento</th>
                    <th class="p-4">Data Doc.</th>
                    <th class="p-4">Descrizione</th>
                    <th class="p-4">Ultimo Pagamento</th>
                    <th class="p-4 text-right">Totale Pagato</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#dbe0e6] dark:divide-gray-700">
                <?php foreach($documents as $d): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-[#2a3441] transition-colors cursor-pointer" onclick="window.location='/document?id=<?= $d['id'] ?>'">
                    <td class="p-4 font-medium text-primary">
                        <?= htmlspecialchars($d['document_number']) ?>
                        <?php if($d['type'] === 'CREDIT_NOTE'): ?>
                            <span class="ml-2 text-[10px] bg-orange-100 text-orange-800 px-1.5 py-0.5 rounded border border-orange-200">NC</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-sm text-[#111418] dark:text-white">
                        <?= date('d/m/Y', strtotime($d['document_date'])) ?>
                    </td>
                    <td class="p-4 text-sm text-[#617589] dark:text-gray-300 truncate max-w-[200px]">
                        <?= htmlspecialchars($d['description']) ?>
                    </td>
                    <td class="p-4 text-sm text-[#617589] dark:text-gray-300">
                        <?= $d['last_payment'] ? date('d/m/Y', strtotime($d['last_payment'])) : '-' ?>
                    </td>
                    <td class="p-4 text-right font-mono font-bold text-[#111418] dark:text-white">
                        <?= number_format($d['total_paid'], 2, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>