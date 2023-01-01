var storage = {},
    nombreChamps = 0,
    formulaire = document.querySelector('.formulaire');

function initialiser() {
    var elements = document.querySelectorAll('.carteExercice input'),
        elementsLength = elements.length,
        carte = document.querySelector('.carteExercice');
    
    for (var i = 0; i < elementsLength; i++) {
        elements[i].addEventListener('mousedown', function(e) {
            var s = storage;
            s.target = e.target.parentNode.parentNode.parentNode;
            s.offsetX = e.clientX - s.target.offsetLeft;
            s.offsetY = e.clientY - s.target.offsetTop;
        });
        
        elements[i].addEventListener('mouseup', function(e) {
            storage = {};
        });
        
        elements[i].addEventListener('change', function(e) {
            var champPositionX = e.target.parentElement.nextElementSibling.firstElementChild,
                champPositionY = e.target.parentElement.nextElementSibling.nextElementSibling.firstElementChild;
            
            champPositionX.value = parseInt(/\d*/.exec(e.target.parentNode.parentNode.parentNode.style.left)[0]);
            champPositionY.value = parseInt(/\d*/.exec(e.target.parentNode.parentNode.parentNode.style.top)[0]);
            console.log(champPositionX.value);
        });
    }
    
    document.addEventListener('mousemove', function(e) {
        var hauteurCarte = parseInt(/\d*/.exec(carte.offsetHeight)[0]),
            largeurCarte = parseInt(/\d*/.exec(carte.offsetWidth)[0]);
        
        var target = storage.target;
        if (target) {
            target.style.top = (e.clientY - storage.offsetY - parseInt(/\d*/.exec(formulaire.offsetTop)[0]) - storage.target.offsetHeight * (nombreChamps -1)) / hauteurCarte * 100  + '%';
            target.style.left = (e.clientX - storage.offsetX - parseInt(/\d*/.exec(formulaire.offsetLeft)[0])) / largeurCarte * 100 + '%';
        }
    });
    
}

initialiser();

var $collectionHolder;
var $emplacementBoutonAjout;
var $addTagButton = $('<button type="button" class="add_tag_link">Ajouter un trou</button>');
var $newLinkLi = $('<li></li>').append($addTagButton);

jQuery(document).ready(function() {
    $collectionHolder = $('ul.carte-liste-formulaire');
    $emplacementBoutonAjout = $('div.ajouter-un-trou');

    
    
    $collectionHolder.append($newLinkLi);
    $emplacementBoutonAjout.append($addTagButton);

    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addTagButton.on('click', function(e) {
        addTagForm($collectionHolder, $newLinkLi);
        initialiser();
    });
});

function addTagForm($collectionHolder, $newLinkLi) {
    var prototype = $collectionHolder.data('prototype');

    var index = $collectionHolder.data('index');
    var newForm = prototype;
    
    newForm = newForm.replace(/__name__/g, index);
    $collectionHolder.data('index', index + 1);

    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
    var hauteurCarte = parseInt(/\d*/.exec(carte.offsetHeight)[0]),
        largeurCarte = parseInt(/\d*/.exec(carte.offsetWidth)[0]);
    
    $newFormLi.css("top", (0 - $newFormLi.height() * nombreChamps) / hauteurCarte * 100  + '%');
    $newFormLi.css("left", '0%');
    nombreChamps++;
    
}