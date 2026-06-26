<x-layout>

    <div class="article-create-wrapper m-0 mt-4">
        
        <div class="card bg-navbar border-neon-cyan shadow-lg p-4" style="width: 100%; max-width: 650px;">
            
            <div class="card-header border-0 bg-transparent pb-0">
                <h3 class="fw-black text-white text-uppercase tracking-tight mb-1">
                    Inserisci <span class="text-glow text-neon-cyan">Annuncio</span>
                </h3>
                <p class="text-muted-custom fs-7 mb-0">
                    Compila i campi sottostanti per pubblicare la tua commissione su Presto.it
                </p>
            </div>

            <div class="card-body pt-4">

                
                <livewire:create-article-form />
                
            </div>
            
        </div>
        
    </div>

</x-layout>