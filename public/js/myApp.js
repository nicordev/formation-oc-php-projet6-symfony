var myApp = {

    eventTarget: {

        /**
         * Hide the target of the event
         *
         * @param event
         */
        hide: function (event) {
            event.target.style.display = "none";
        }
    },

    elementTool: {

        /**
         * Add a button
         *
         * @param parentElement
         * @param text
         * @param callback
         * @param parameters
         * @param classes
         */
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

        /**
         * Remove an element
         *
         * @param elementToRemove
         */
        removeElement: function (elementToRemove) {
            elementToRemove.remove();
        }
    }
};