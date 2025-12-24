<!-- Main Content -->
<main class="flex-1 flex justify-center py-10 px-4 md:px-10">
    <div class="w-full max-w-[800px] flex flex-col gap-6">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-[#617589]">
            <a class="hover:text-primary flex items-center gap-1 text-decoration-none text-[#617589]" href="/suppliers">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Torna alla lista
            </a>
            <span>/</span>
            <span>Fornitori</span>
            <span>/</span>
            <span class="text-[#111418] font-medium">Modifica</span>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-xl border border-[#dbe0e6] shadow-sm overflow-hidden flex flex-col">
            <!-- Card Header -->
            <div class="px-6 py-5 border-b border-[#f0f2f4] bg-white">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-3">
                        <div class="bg-primary/10 p-2 rounded-lg text-primary">
                            <span class="material-symbols-outlined">edit_document</span>
                        </div>
                        <h1 class="text-[#111418] tracking-tight text-xl md:text-2xl font-bold leading-tight m-0">
                            Modifica: <?= htmlspecialchars($supplier['amazon_supplier_site_name']) ?>
                        </h1>
                    </div>
                    <p class="text-[#617589] text-sm font-normal leading-normal pl-[52px] m-0">
                        Aggiorna i dettagli di configurazione e le impostazioni del foglio di calcolo.
                    </p>
                </div>
            </div>

            <!-- Form Content -->
            <form action="/suppliers/update" method="POST">
                <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
                
                <div class="p-6 md:p-8 flex flex-col gap-6">
                    <!-- Row 1: Nome Visualizzato -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[#111418] text-sm font-medium leading-normal">Nome Visualizzato</label>
                        <input name="name" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe0e6] bg-white focus:border-primary h-12 placeholder:text-[#617589] px-4 text-base font-normal leading-normal transition-all" type="text" value="<?= htmlspecialchars($supplier['name'] ?? '') ?>" />
                    </div>

                    <!-- Row 2: Google Spreadsheet ID -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[#111418] text-sm font-medium leading-normal flex items-center gap-2">
                            Google Spreadsheet ID
                            <span class="material-symbols-outlined text-[#617589] text-[16px] cursor-help" title="L'ID univoco del foglio Google Sheet">help</span>
                        </label>
                        <div class="relative">
                            <input name="google_spreadsheet_id" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe0e6] bg-white focus:border-primary h-12 placeholder:text-[#617589] px-4 text-base font-normal leading-normal font-mono text-sm transition-all pr-10" type="text" value="<?= htmlspecialchars($supplier['google_spreadsheet_id'] ?? '') ?>" placeholder="es. 1BxiMVs0XRA5nFMdKbBdB..." />
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-[#617589]">
                                <span class="material-symbols-outlined text-[20px]">content_copy</span>
                            </div>
                        </div>
                        <p class="text-[#617589] text-xs m-0">L'ID che trovi nell'URL del foglio (es. tra /d/ e /edit).</p>
                    </div>

                    <!-- Row 3: Tabs Configuration -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-[#111418] text-sm font-medium leading-normal">Nome Tab Fatture</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#617589]">
                                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                                </span>
                                <input name="google_sheet_invoices" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe0e6] bg-white focus:border-primary h-12 placeholder:text-[#617589] pl-10 pr-4 text-base font-normal leading-normal transition-all" type="text" value="<?= htmlspecialchars($supplier['google_sheet_invoices'] ?? '') ?>" placeholder="es. Fatture" />
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-[#111418] text-sm font-medium leading-normal">Nome Tab Note Credito</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#617589]">
                                    <span class="material-symbols-outlined text-[20px]">credit_card_off</span>
                                </span>
                                <input name="google_sheet_credit_notes" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe0e6] bg-white focus:border-primary h-12 placeholder:text-[#617589] pl-10 pr-4 text-base font-normal leading-normal transition-all" type="text" value="<?= htmlspecialchars($supplier['google_sheet_credit_notes'] ?? '') ?>" placeholder="es. Note Credito" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="px-6 py-4 bg-[#f9fafb] border-t border-[#f0f2f4] flex flex-col-reverse md:flex-row justify-end gap-3">
                    <a href="/suppliers" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-white border border-[#dbe0e6] text-[#111418] hover:bg-[#f0f2f4] text-sm font-bold leading-normal tracking-[0.015em] transition-colors shadow-sm text-decoration-none">
                        <span class="truncate">Annulla</span>
                    </a>
                    <button type="submit" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-6 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold leading-normal tracking-[0.015em] transition-colors shadow-sm gap-2 border-0">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span class="truncate">Salva Modifiche</span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Info -->
        <div class="flex justify-between text-xs text-[#617589] px-2">
            <p>ID Sistema: #<?= $supplier['id'] ?></p>
            <p>Valuta: <?= htmlspecialchars($supplier['currency']) ?></p>
        </div>
    </div>
</main>