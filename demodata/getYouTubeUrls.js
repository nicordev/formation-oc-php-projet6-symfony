/*
 * Code à executer dans une ardoise javascript ici : https://www.youtube.com/results?search_query=snowboard+tricks
 */

/**
 *  Créé un fichier
 *
 *  @param {Object} file un objet pouvant être vide qui va contenir le fichier
 *  @param {string} content le contenu du fichier à créer
 *  @param {string} type = 'md' le type de fichier
 */
function makeFile(content, type = 'plain') {

    var data = new Blob([content], {type: 'text/' + type});

    if (file !== null) {
        window.URL.revokeObjectURL(file);
    }
    file = window.URL.createObjectURL(data);
    return file;
}

function showUrls($file) {
  var dlLinkElt = document.createElement("a");
  
  dlLinkElt.href = $file;
  dlLinkElt.textContent = "Afficher les liens";
  
  document.body.innerHTML = "";
  document.body.appendChild(dlLinkElt);
}

var linkElts = document.querySelectorAll("h3.title-and-badge.style-scope.ytd-video-renderer a"),
    links = [];

for (var i = 0, size = linkElts.length; i < size; i++) {
  links.push(linkElts[i].href);
}

var file = makeFile(links.join("\n"));
  
showUrls(file);
