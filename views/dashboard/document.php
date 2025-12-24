<div class="w-full max-w-[800px] mx-auto flex flex-col gap-6 py-8 px-4">
    
    <div class="flex items-center gap-2 text-sm text-[#617589]">
        <a href="javascript:history.back()" class="hover:text-primary flex items-center gap-1 text-decoration-none text-[#617589]">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Indietro
        </a>
        <span>/</span>
        <span class="text-[#111418] font-medium">Documento</span>
    </div>

    <!-- Info Documento -->
    <div class="bg-white dark:bg-[#1e2732] rounded-xl border border-[#dbe0e6] dark:border-gray-700 p-6 shadow-sm">
        <div class="flex justify-between items-start mb-6">
            <div>
                <div class="text-sm text-[#617589] uppercase font-bold tracking-wider mb-1"><?= $doc['type'] === 'INVOICE' ? 'Fattura' : 'Nota di Credito' ?></div>
                <h1 class="text-3xl font-black text-[#111418] dark:text-white"><?= htmlspecialchars($doc['document_number']) ?></h1>
            </div>
            <div class="text-right">
                <a href="/supplier?id=<?= $doc['supplier_id'] ?>" class="text-primary font-medium hover:underline"><?= htmlspecialchars($doc['supplier_name']) ?></a>
                <div class="text-sm text-[#617589]"><?= date('d M Y', strtotime($doc['document_date'])) ?></div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-[#232d38] rounded-lg p-4 border border-[#dbe0e6] dark:border-gray-700">
            <h3 class="text-sm font-bold text-[#111418] dark:text-white mb-3">Cronologia Transazioni</h3>
            
            <div class="flex flex-col gap-3">
                <?php 
                $totalPaid = 0;
                foreach($history as $h): 
                    $totalPaid += $h['amount'];
                    $isNegative = $h['amount'] < 0;
                ?>
                <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-600 pb-2 last:border-0 last:pb-0">
                    <div class="flex flex-col">
                        <a href="/remittance?id=<?= $h['remittance_id'] ?>" class="text-sm font-medium text-primary hover:underline">
                            Remittance del <?= date('d/m/Y', strtotime($h['received_at'])) ?>
                        </a>
                        <span class="text-xs text-[#617589]"><?= htmlspecialchars($h['subject']) ?></span>
                    </div>
                    <div class="font-mono font-bold <?= $isNegative ? 'text-red-600' : 'text-green-600' ?>">
                        <?= number_format($h['amount'], 2, ',', '.') ?> <?= $doc['currency'] ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 pt-3 border-t border-gray-300 dark:border-gray-600 flex justify-between items-center">
                <span class="font-bold text-[#111418] dark:text-white">Totale Saldato/Stornato</span>
                <span class="font-black text-xl text-[#111418] dark:text-white"><?= number_format($totalPaid, 2, ',', '.') ?> <?= $doc['currency'] ?></span>
            </div>
        </div>
    </div>
</div>