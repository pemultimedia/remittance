<!-- Contenitore Login -->
<div class="w-full max-w-[480px] flex flex-col items-center animate-fade-in-up">
    
    <!-- Logo / Header sopra la card -->
    <div class="mb-8 flex items-center gap-3">
        <div class="size-10 bg-primary/10 rounded-lg flex items-center justify-center text-primary shadow-sm">
            <svg fill="none" height="24" viewbox="0 0 48 48" width="24" xmlns="http://www.w3.org/2000/svg">
                <path clip-rule="evenodd" d="M24 4H6V17.3333V30.6667H24V44H42V30.6667V17.3333H24V4Z" fill="currentColor" fill-rule="evenodd"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-[#111418] dark:text-white m-0">Remittance Manager</h1>
    </div>
    
    <!-- Card Login -->
    <div class="w-full bg-white dark:bg-[#1e2732] rounded-xl shadow-lg border border-[#e5e7eb] dark:border-[#2a3441] overflow-hidden">
        <div class="p-8 md:p-10 flex flex-col gap-6">
            
            <!-- Titolo Card -->
            <div class="flex flex-col gap-2 text-center sm:text-left">
                <h2 class="text-xl font-bold leading-tight text-[#111418] dark:text-white">Benvenuto</h2>
                <p class="text-sm text-[#617589] dark:text-[#94a3b8]">Inserisci le tue credenziali per accedere.</p>
            </div>

            <!-- Form -->
            <form action="/login" method="POST" class="flex flex-col gap-5">
                
                <!-- Email -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium leading-normal text-[#111418] dark:text-[#e2e8f0]" for="email">Email</label>
                    <div class="relative">
                        <input name="email" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe0e6] dark:border-[#3f4a56] bg-white dark:bg-[#101922] focus:border-primary h-12 placeholder:text-[#617589] dark:placeholder:text-[#64748b] px-4 text-base font-normal leading-normal transition-colors" id="email" placeholder="nome@esempio.com" type="email" required />
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-[#617589] dark:text-[#64748b] pointer-events-none">
                            <span class="material-symbols-outlined text-[20px]">mail</span>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <label class="text-sm font-medium leading-normal text-[#111418] dark:text-[#e2e8f0]" for="password">Password</label>
                    </div>
                    <div class="relative">
                        <input name="password" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dbe0e6] dark:border-[#3f4a56] bg-white dark:bg-[#101922] focus:border-primary h-12 placeholder:text-[#617589] dark:placeholder:text-[#64748b] px-4 text-base font-normal leading-normal transition-colors" id="password" placeholder="••••••••" type="password" required />
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-[#617589] dark:text-[#64748b] cursor-pointer hover:text-[#111418] dark:hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-[20px]">lock</span>
                        </div>
                    </div>
                </div>

                <!-- Bottone Submit -->
                <button type="submit" class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 bg-primary hover:bg-blue-600 transition-colors text-white text-base font-bold leading-normal tracking-[0.015em] shadow-md mt-2 border-0">
                    <span class="truncate">Accedi</span>
                </button>
            </form>
        </div>
        
        <!-- Footer Card -->
        <div class="bg-gray-50 dark:bg-[#17202a] px-8 py-4 border-t border-[#e5e7eb] dark:border-[#2a3441] text-center">
            <p class="text-xs text-[#617589] dark:text-[#94a3b8] m-0">
                Accesso riservato al personale autorizzato.
            </p>
        </div>
    </div>
</div>