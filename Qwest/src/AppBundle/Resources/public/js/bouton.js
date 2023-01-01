var bouton = document.getElementById("bouton-aide");

if(bouton != null){
    var aide = document.getElementById("aide");
    
    bouton.addEventListener("click", function(){
        if(getComputedStyle(aide).display === "none"){
            aide.style.display = "inline-block";
        }
        
        else{
            aide.style.display = "none";
        }
        
    });
    
    aide.addEventListener("click", function(){
        aide.style.display = "none";
    });
}

