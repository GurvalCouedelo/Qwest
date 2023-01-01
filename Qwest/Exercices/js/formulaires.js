//var suffixeAjax = "http:/" + "/127.0.0.1/Qwest/Exercices/index.php/";
var suffixeAjax = "https:/" + "/exercices.titann.fr/index.php/";
var body = document.querySelector('body');

window.formulaireCourant = null;
window.formulaireCourantInputs = null;
window.formulaireCourantData = {};


function initialiserFormulaire(){
    var formulaires = document.querySelectorAll('form');
    
    for(var i = 0; i < formulaires.length; i++){
        formulaires[i].addEventListener("mouseup", function(e){
            if(e.currentTarget.getAttribute("id_entite") != null){
                if(window.formulaireCourant != null){
                    window.ancienFormulaireCourant = window.formulaireCourant
                }
                    window.formulaireCourant = e.currentTarget;
            }
            
        }, true);
    }
}

function creerFormData(ancien = false){
    var xhr = new XMLHttpRequest();
    
    if(ancien == false){
        var formData = new FormData(window.formulaireCourant),
            suffixeDom = "#" + window.formulaireCourant.getAttribute("id");
    }
    else{
        var formData = new FormData(window.ancienFormulaireCourant),
            suffixeDom = "#" + window.ancienFormulaireCourant.getAttribute("id");
    }
    
    window.formulaireCourantInputs = document.querySelectorAll(
        suffixeDom + ' input[type=\"text\"], ' + suffixeDom + ' input[type=\"number\"], ' + suffixeDom + ' input[type=\"radio\"], ' + suffixeDom + ' input[type=\"checkbox\"], ' + suffixeDom + ' textarea'
    );
    
    for(var i = 0; i < window.formulaireCourantInputs.length; i++){
        
        if(window.formulaireCourantInputs[i].name == "vraiFaux"){
            formData.append(window.formulaireCourantInputs[i].name, window.formulaireCourantInputs[i].checked);
            
        }
        
        else if(window.formulaireCourantInputs[i].name == "radioVraiFaux"){
            if(window.formulaireCourantInputs[i].checked === true){
                 formData.append(window.formulaireCourantInputs[i].name, window.formulaireCourantInputs[i].value);
            }
        }
        
        else{
            formData.append(window.formulaireCourantInputs[i].name, window.formulaireCourantInputs[i].value);
        }
    }
        
    return formData;
}


body.addEventListener("mouseup", function(){
    if (window.formulaireCourant != null && window.intro == false){
        //        Vérification de la modification du formulaire
        
        var token = window.formulaireCourant.getAttribute("form_token"),
            formA = window.formulaireCourantData[token],
            formB = creerFormData();

        if(formA != null){
            var different = false;

            for (let [key, value] of formB.entries()) {
                if (formA.get(key) !== value){
                    different = true;
                } 
            }
        }

        window.formulaireCourantData[token] = formB;

        if(window.ancienFormulaireCourant != undefined && window.formulaireCourant != window.ancienFormulaireCourant){
            //Enregistrer ancien formulaire

            var ancienFormulaire = creerFormData(true);
            enregistrer(ancienFormulaire, true);
        }

        else{
            if(different == true){
                enregistrer(formB);
            }
        }

    //    Gestion des cases à cocher

        var boutonsSpeciaux = document.querySelectorAll("input[type=\"checkbox\"], input[type=\"radio\"]");

        for(var i = 0; i < boutonsSpeciaux.length; i++){
            boutonsSpeciaux[i].addEventListener("change", function(){
                var formulaireActualise = creerFormData();
                enregistrer(formulaireActualise);
            });
        }
    }
    
    else if(window.intro == true){
        envoyerIntro();
    }
});


function enregistrer(formData, ancien = false){
    var xhr = new XMLHttpRequest();
    
    if(ancien == false){
        xhr.open('POST', suffixeAjax + "enregistrer/" + window.formulaireCourant.getAttribute("type_formulaire") + "/" + window.formulaireCourant.getAttribute("id_entite"));
        xhr.send(formData);
        
        xhr.addEventListener("readystatechange", function(){
            if(xhr.readyState === 4){
                console.log(window.formulaireCourant.getAttribute("id_entite"));
                var bandeActualisation = document.querySelector(".bande-actualisation");
                bandeActualisation.innerHTML = xhr.responseText;
                bandeActualisation.style = "color: #28e328;";
            }                     
        });
    }
    
    else{
        xhr.open('POST', suffixeAjax + "enregistrer/" + window.ancienFormulaireCourant.getAttribute("type_formulaire") + "/" + window.ancienFormulaireCourant.getAttribute("id_entite"));
        xhr.send(formData);
        xhr.addEventListener("readystatechange", function(){
            if(xhr.readyState === 4){
                var bandeActualisation = document.querySelector(".bande-actualisation");
                bandeActualisation.innerHTML = xhr.responseText;
                bandeActualisation.style = "color: #28e328;";

            }                     
        });
    }
}

function envoyerIntro(){
    var xhr = new XMLHttpRequest(),
        formulaireIntro = document.querySelector("#formulaireIntro");
    
    console.log(formulaireIntro);
    
    var formData = new FormData(formulaireIntro),
        formulaireIntroInputs = document.querySelectorAll("#formulaireIntro textarea, #formulaireIntro text");
    
    for(var i = 0; i < formulaireIntroInputs.length; i++){
        formData.append(formulaireIntroInputs[i].name, formulaireIntroInputs[i].value);
    }
    
    
    xhr.open('POST', suffixeAjax + "enregistrer/intro/" + window.id);
    xhr.send(formData);
        
    xhr.addEventListener("readystatechange", function(){
        if(xhr.readyState === 4){
            console.log(xhr.responseText);
            
            var bandeActualisation = document.querySelector(".bande-actualisation");
            bandeActualisation.innerHTML = xhr.responseText;
            bandeActualisation.style = "color: #28e328;";
        }                     
    });
}
