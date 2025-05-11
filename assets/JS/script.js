document.addEventListener('DOMContentLoaded', () => {
    // Sélection des éléments du DOM
    

    const nav = document.querySelector('nav');
    const footer = document.querySelector('footer');
    const sectionCachee = document.querySelector('.sectionCachee');
    const accueilGlobal = document.querySelector('#accueilGlobal');
    const mainIndex = document.querySelector('.mainIndex');
    const iconeNav = document.querySelectorAll('.iconeNavIndex');
    const pathname = window.location.pathname; // Récupérer le chemin de l'URL actuelle
    


    // gestion de la couleur de la barre de navigation et du footer sur la page d'accueil
    if (pathname === '/' || pathname === '/home') {
        const barreMin = document.querySelector('#barreMin');
        const barreMax = document.querySelector('#barreMax');
        const chiffreMin = document.querySelector('#chiffreMin');
        const chiffreMax = document.querySelector('#chiffreMax');
    
        const inputAnneeMin = document.querySelector('#inputAnneeMin');
        const inputAnneeMax = document.querySelector('#inputAnneeMax');
        const barreAnneeMin = document.querySelector('#barreAnneeMin');
        const barreAnneeMax = document.querySelector('#barreAnneeMax');
        
    
        // Constantes pour les attributs min/max de la note (pour éviter de les lire à chaque fois)
        const barreMinVal = parseFloat(barreMin.min);
        const barreMaxVal = parseFloat(barreMin.max); // min et max sont les mêmes pour les deux barres
    
        // Constantes pour les attributs min/max de l'année(pour éviter de les lire à chaque fois)
        const barreAnneeMinVal = parseFloat(barreAnneeMin.min);
        const barreAnneeMaxVal = parseFloat(barreAnneeMin.max); // min et max sont les mêmes pour les deux barres
    
        // Fonction pour mettre à jour les inputs numériques depuis les barres de sélection
        function updateNumberInputs(inputMin, inputMax, barreMin, barreMax) {
            inputMin.value = barreMin.value;
            inputMax.value = barreMax.value;
        }
    
        // Fonction pour gérer le mouvement de la barre MIN
        function handleMinSlider(barreMini, barreMaxi, inputMini) {
            // Convertir les valeurs en nombres pour la comparaison
            let minValue = parseFloat(barreMini.value);
            let maxValue = parseFloat(barreMaxi.value);
    
            // Empêcher la barre MIN de dépasser la barre MAX
            // On utilise une petite marge si besoin (ex: 1) ou 0 si elles peuvent se toucher
            const gap = 0; // Mettez une valeur > 0 si vous voulez un écart minimum obligatoire
            if (minValue > maxValue - gap) {
                barreMini.value = maxValue - gap; // Ajuster la valeur de la barre min
                minValue = parseFloat(barreMini.value); // Mettre à jour la variable locale
            }
    
            // Mettre à jour l'input numérique correspondant
            inputMini.value = minValue;
        }
    
        // Fonction pour gérer le mouvement de la barre MAX
        function handleMaxSlider(barreMini, barreMaxi, inputMax) {
            // Vérifier si la barre MIN est supérieure à la barre MAX                               
            // Convertir les valeurs en nombres pour la comparaison
            let minValue = parseFloat(barreMini.value);
            let maxValue = parseFloat(barreMaxi.value);
    
            // Empêcher la barre MAX d'aller en dessous de la barre MIN
            const gap = 0; // Doit être la même valeur que dans handleMinSlider
            if (maxValue < minValue + gap) {
                barreMaxi.value = minValue + gap; // Ajuster la valeur de la barre max
                maxValue = parseFloat(barreMaxi.value); // Mettre à jour la variable locale
            }
    
            // Mettre à jour l'input numérique correspondant
            inputMax.value = maxValue;
        }
    
         // Fonction pour gérer la saisie dans l'input numérique MIN
         function handleMinNumberInput(inputMini, barreMini, barreMaxi, barreMiniVal, barreMaxiVal) {
            let minNumValue = parseFloat(inputMini.value);
            let maxSliderValue = parseFloat(barreMaxi.value);
    
            // Validation basique: Vérifier si c'est un nombre et dans les limites globales
            if (isNaN(minNumValue)) {
                minNumValue = parseFloat(barreMini.value); // Revenir à la valeur de la barre si invalide
            }
            if (minNumValue < barreMiniVal) {
                minNumValue = barreMiniVal;
            }
            if (minNumValue > barreMaxiVal) {
                 minNumValue = barreMaxiVal;
            }
    
            // Empêcher la valeur MIN de dépasser la valeur MAX actuelle
            const gap = 0;
            if (minNumValue > maxSliderValue - gap) {
                minNumValue = maxSliderValue - gap;
            }
    
            // Mettre à jour l'input (au cas où on a corrigé la valeur) et la barre
            inputMini.value = minNumValue;
            barreMini.value = minNumValue;
        }
    
         // Fonction pour gérer la saisie dans l'input numérique MAX
         function handleMaxNumberInput(inputMax, barreMini, barreMaxi, barreMinVal, barreMaxVal) {
            let maxNumValue = parseFloat(inputMax.value);
            let minSliderValue = parseFloat(barreMini.value);
    
            // Validation basique: Vérifier si c'est un nombre et dans les limites globales
            if (isNaN(maxNumValue)) {
                 maxNumValue = parseFloat(barreMaxi.value); // Revenir à la valeur de la barre si invalide
            }
             if (maxNumValue > barreMaxVal) {
                maxNumValue = barreMaxVal;
            }
            if (maxNumValue < barreMinVal) {
                 maxNumValue = barreMinVal;
            }
    
    
            // Empêcher la valeur MAX d'être inférieure à la valeur MIN actuelle
            const gap = 0;
            if (maxNumValue < minSliderValue + gap) {
                maxNumValue = minSliderValue + gap;
            }
    
            // Mettre à jour l'input (au cas où on a corrigé la valeur) et la barre
            inputMax.value = maxNumValue;
            barreMaxi.value = maxNumValue;
        }
    
    
        // --- Initialisation ---
        // initialisation des barres et des inputs numériques pour les notes
        // S'assurer que les valeurs initiales des inputs numériques correspondent aux barres
        updateNumberInputs(chiffreMin, chiffreMax, barreMin, barreMax);
        
        // S'assurer que les contraintes initiales sont respectées (au cas où le HTML serait invalide)
        handleMinSlider(barreMin, barreMax, chiffreMin); // Vérifie min vs max
        handleMaxSlider(barreMin, barreMax, chiffreMax); // Vérifie max vs min
    
            // initialisation des barres et des inputs numériques pour l'année'
            updateNumberInputs(inputAnneeMin, inputAnneeMax, barreAnneeMin, barreAnneeMax);
            handleMinSlider(barreAnneeMin, barreAnneeMax, inputAnneeMin); // Vérifie min vs max
            handleMaxSlider(barreAnneeMin, barreAnneeMax, inputAnneeMax); // Vérifie max vs min
    
    
        // --- Ajout des écouteurs d'événements ---
        // Écouter l'événement 'input' pour une mise à jour en temps réel pendant le glissement/saisie de la note
        barreMin.addEventListener('input', () => handleMinSlider(barreMin, barreMax, chiffreMin));
        barreMax.addEventListener('input', () => handleMaxSlider(barreMin, barreMax, chiffreMax));
        chiffreMin.addEventListener('input', () => handleMinNumberInput(chiffreMin, barreMin, barreMax, barreMinVal, barreMaxVal));
        chiffreMax.addEventListener('input', () => handleMaxNumberInput(chiffreMax, barreMin, barreMax, barreMinVal, barreMaxVal));
    
            // Écouter l'événement 'input' pour une mise à jour en temps réel pendant le glissement/saisie de la note
        barreAnneeMin.addEventListener('input', () => handleMinSlider(barreAnneeMin, barreAnneeMax, inputAnneeMin));
        barreAnneeMax.addEventListener('input', () => handleMaxSlider(barreAnneeMin, barreAnneeMax, inputAnneeMax));
        inputAnneeMin.addEventListener('input', () => handleMinNumberInput(inputAnneeMin, barreMin, barreAnneeMax, barreAnneeMinVal, barreAnneeMaxVal));
        inputAnneeMax.addEventListener('input', () => handleMaxNumberInput(inputAnneeMax, barreMin, barreAnneeMax, barreAnneeMinVal, barreAnneeMaxVal));
    
    
        // Optionnel: écouter 'change' pour une dernière validation quand l'input perd le focus
        // chiffreMin.addEventListener('change', handleMinNumberInput);
        // chiffreMax.addEventListener('change', handleMaxNumberInput);

    const chevron = document.querySelector('.chevron');
    

    chevron.addEventListener('click', () => {
        nav.classList.add('color-changed');
        nav.style.height = "15vh";
        footer.classList.add('color-changed');
        mainIndex.style.height = "70vh";
        footer.style.height = "15vh";
        iconeNav.forEach(icone => {
            icone.style.color = "black";
            
        });
        sectionCachee.classList.add('visible');
        accueilGlobal.classList.remove('accueilGlobal'); // Add .visible class instead
    }, { once: true }); // L'écouteur sera automatiquement retiré après le premier clic
    }

    if (pathname === '/film') {
    
        console.log('film.php loaded');
        
         nav.classList.add('color-changed');
         footer.classList.add('color-changed');
             
     }

    if (pathname === '/list') {
    
        console.log('list.php loaded');
        
        nav.classList.add('color-changed');
        footer.classList.add('color-changed');

        const filmInList = document.querySelectorAll('.filmInList');
        const ratings = document.querySelectorAll('.rating');

//fonction changer la couleur des étoiles en fonction de la note
function changeStarColor(rating) {
    if(rating.textContent <5.5){
        rating.querySelector('.bi-star-fill').style.color = "red";
    }else if(rating.textContent <6){
        rating.querySelector('.bi-star-fill').style.color = "orange";
    }else if(rating.textContent < 6.5){
        rating.querySelector('.bi-star-fill').style.color = "yellow";
    }else if(rating.textContent < 7){
        rating.querySelector('.bi-star-fill').style.color = "lightgreen";
    }else if(rating.textContent < 7.5){
        rating.querySelector('.bi-star-fill').style.color = "lime";
    }else{
        rating.querySelector('.bi-star-fill').style.color = "DeepSkyBlue";
    }
}

// change la couleur des étoiles en fonction de la note dans la liste
        ratings.forEach(rating => {
        
            changeStarColor(rating);
        
        });


        
        

        filmInList.forEach(film => {
            film.addEventListener('click', () => {
                // Récupérer les informations du film dans la liste
                let poster = film.querySelector('.posterFilmList');
                let title = film.querySelector('.title');
                let synopsis = film.querySelector('.synopsis');
                let addedAt = film.querySelector('.addedAt');
                let genre = film.querySelector('.genre');
                let duration = film.querySelector('.duration');
                let rating = film.querySelector('.rating');
                let ratings = film.querySelectorAll('.rating');
                

                // cible les emplacements de l'affichage du film
                let posterFilm = document.querySelector('#posterShow');
                let titleFilm = document.querySelector('#titleShow');
                let synopsisFilm = document.querySelector('#synopsisShow');
                let addedAtFilm = document.querySelector('#addedAtShow');
                let genreFilm = document.querySelector('#genreShow');
                let durationFilm = document.querySelector('#durationShow');
                let ratingFilm = document.querySelector('#ratingShow');
                

                console.log(poster.src);
                console.log(synopsisFilm.textContent);
                
                ratingCreate = document.createElement('i')
                ratingCreate.classList.add('bi', 'bi-star-fill', 'px-1');
// Afficher les informations du film
                titleFilm.textContent = title.textContent;
                synopsisFilm.textContent = synopsis.textContent;
                addedAtFilm.textContent = "Ajouté le : " + addedAt.textContent;
                ratingFilm.textContent = rating.textContent;
                ratingFilm.appendChild(ratingCreate);
                genreFilm.textContent = genre.textContent;
                durationFilm.textContent = duration.textContent;
                posterFilm.src = poster.src;

                // change la couleur des étoiles en fonction de la note côté affichage du film

                changeStarColor(document.querySelector('#ratingShow'));
                
                
               
                
            });
        });





             
     }
     
}   


);

// gestion de la couleur de la barre de navigation et du footer sur d'autres pages
