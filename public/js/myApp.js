var myApp = {

    eventTarget: {

        hide: function (event) {
            event.target.style.display = "none";
        }
    },

    elementTool: {

        addButton: function (parentElement, text, callback, parameters = null, classes = "btn btn-primary") {
            var btnElt = document.createElement("button");

            btnElt.textContent = text;
            btnElt.type = "button";
            btnElt.setAttribute("class", classes);

            btnElt.addEventListener("click", function () {
                callback(parameters);
            });

            parentElement.appendChild(btnElt);
        },

        removeElement: function (elementToRemove) {
            elementToRemove.remove();
        }
    }
};