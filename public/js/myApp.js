var myApp = {

    switchNavigationMenu: function (event) {

        var navigationMenuElt = document.getElementById("navigation-menu");

        if (navigationMenuElt) {
            navigationMenuElt.remove();
        } else {
            buildNavigationMenu(event.target.parentElement);
        }

        /**
         * Create a navigation menu
         *
         * @param parentElt
         */
        function buildNavigationMenu(parentElt) {

            var navigationLinkElts = document.getElementsByClassName("nav-link"),
                menuWrapperElt = document.createElement("div"),
                menuElt = document.createElement("ul");

            menuElt.classList.add("list-group");
            for (let navLinkElt of navigationLinkElts) {
                menuElt.appendChild(buildMenuItem(navLinkElt));
            }

            menuWrapperElt.classList.add("main-navigation-menu");
            menuWrapperElt.appendChild(menuElt);
            menuWrapperElt.id = "navigation-menu";

            parentElt.parentElement.appendChild(menuWrapperElt);

            /**
             * Make a li element with a link
             *
             * @param linkElt
             * @param menuElt
             */
            function buildMenuItem(linkElt) {

                let liElt = document.createElement("li"),
                    linkCloneElt = linkElt.cloneNode(true);

                liElt.classList.add("list-group-item");
                liElt.classList.add("background-color-1");
                linkCloneElt.classList.add("color-1");
                liElt.appendChild(linkCloneElt);

                return liElt;
            }
        }
    },

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