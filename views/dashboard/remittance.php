<div class="w-full max-w-[1000px] mx-auto flex flex-col gap-6 py-8 px-4">
    
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-[#617589]">
        <a href="/dashboard" class="hover:text-primary flex items-center gap-1 text-decoration-none text-[#617589]">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Dashboard
        </a>
        <span>/</span>
        <span class="text-[#111418] font-medium">Dettaglio Pagamento #<?= $meta['id'] ?></span>
    </div>

    <!-- Header Card -->
    <div class="bg-white dark:bg-[#1e2732] rounded-xl border border-[#dbe0e6] dark:border-gray-700 p-6 shadow-sm flex flex-col md:flex-row justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#111418] dark:text-white mb-1"><?= htmlspecialchars($meta['subject']) ?></h1>
            <div class="flex items-center gap-4 text-sm text-[#617589]">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span> 
                    Ricevuto: <?= date('d F Y, H:i', strtotime($meta['received_at'])) ?>
                </span>
                <a href="/supplier?id=<?= $meta['supplier_id'] ?>" class="flex items-center gap-1 hover:text-primary text-decoration-none">
                    <span class="material-symbols-outlined text-[16px]">store</span> 
                    <?= htmlspecialchars($meta['supplier_name']) ?>
                </a>
            </div>
        </div>
        <div class="text-right">
            <div class="text-sm text-[#617589]">Totale Transazioni</div>
            <?php 
                // Ricalcolo totale per sicurezza
                $total = 0;
                foreach($items as $it) $total += $it['amount'];
            ?>
            <div class="text-3xl font-black text-primary"><?= number_format($total, 2, ',', '.') ?> <?= $meta['currency'] ?></div>
        </div>
    </div>

    <!-- Lista Voci -->
    <div class="bg-white dark:bg-[#1e2732] rounded-xl border border-[#dbe0e6] dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-[#dbe0e6] dark:border-gray-700 font-bold text-[#111418] dark:text-white">
            Dettaglio Voci (<?= count($items) ?>)
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-[#232d38] border-b border-[#dbe0e6] dark:border-gray-700 text-xs uppercase text-[#617589] dark:text-gray-400 font-bold">
                        <th class="p-4">Documento</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Descrizione / Note</th>
                        <th class="p-4 text-right">Importo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#dbe0e6] dark:divide-gray-700">
                    <?php if(empty($items)): ?>
                        <tr>
                            <td colspan="4" class="p-6 text-center text-gray-500">
                                Nessun dettaglio trovato per questa remittance.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($items as $item): 
                            $isNegative = $item['amount'] < 0;
                            $amountClass = $isNegative ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400';
                            
                            // Decodifica sicura del JSON
                            $rawDesc = '-';
                            if (!empty($item['raw_data'])) {
                                $decoded = json_decode($item['raw_data'], true);
                                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['description'])) {
                                    $rawDesc = $decoded['description'];
                                }
                            }
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-[#2a3441] transition-colors">
                            <td class="p-4">
                                <?php if(!empty($item['document_id'])): ?>
                                    <a href="/document?id=<?= $item['document_id'] ?>" class="font-medium text-primary hover:underline">
                                        <?= htmlspecialchars($item['document_number']) ?>
                                    </a>
                                <?php else: ?>
                                    <!-- Se non c'è ID documento, mostriamo il numero fattura dal raw data se esiste -->
                                    <span class="text-gray-500">
                                        <?= htmlspecialchars($decoded['invoice_number'] ?? 'N/D') ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-sm">
                                <?php if(($item['doc_type'] ?? '') === 'INVOICE'): ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Fattura</span>
                                <?php elseif(($item['doc_type'] ?? '') === 'CREDIT_NOTE'): ?>
                                    <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded">Nota Credito</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">Altro</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-sm text-[#617589] dark:text-gray-300">
                                <?= htmlspecialchars($rawDesc) ?>
                            </td>
                            <td class="p-4 text-right font-mono font-bold <?= $amountClass ?>">
                                <?= number_format($item['amount'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>