var myTrickEditor = {

    updateMainImagePreview: function (imageUrl) {

        let mainImagePreviewElement = document.getElementById("main-image-preview");

        if (imageUrl !== "") {
            mainImagePreviewElement.src = imageUrl;
        } else {
            mainImagePreviewElement.src = "/img/no_image_text.jpg";
        }
    },

    addDeleteButtons: function () {

        var imageInputElts = document.querySelectorAll('input[id*="trick_images_"]'),
            videoInputElts = document.querySelectorAll('input[id*="trick_videos_"]');

        for (var imageInputElt of imageInputElts) {
            myTrickEditor.addADeleteButton(imageInputElt.parentElement, imageInputElt.parentElement.parentElement.parentElement);
        }

        for (var videoInputElt of videoInputElts) {
            myTrickEditor.addADeleteButton(videoInputElt.parentElement, videoInputElt.parentElement.parentElement.parentElement);
        }
    },

    addADeleteButton: function (parentElement, targetElement) {

        parentElement.appendChild(buildDeleteButton(targetElement));
        addBootstrapClasses(parentElement);

        function buildDeleteButton(targetElt) {

            var btnElt = document.createElement("button"),
                spanElt = document.createElement("span");

            spanElt.setAttribute("aria-hidden", "true");
            spanElt.textContent = "×";

            btnElt.type = "button";
            btnElt.classList.add("close");
            btnElt.setAttribute("arial-label", "Close");

            btnElt.appendChild(spanElt);

            btnElt.addEventListener("click", function () {
                myApp.elementTool.removeElement(targetElt);
            });

            return btnElt;
        }

        function addBootstrapClasses(parentElt) {

            parentElt.classList.add('d-flex');
            parentElt.children[0].classList.add('mr-3');
            parentElt.children[1].classList.add('mr-3');
        }
    },

    /**
     * Add an input field
     *
     * @param wrapperId
     * @param hidden
     * @param placeholder
     * @returns {DOM Element} the input field
     */
    addInput: function (wrapperId, hidden = false, placeholder = null) {

        var wrapperElt = document.getElementById(wrapperId),
            lastKey = wrapperElt.children.length,
            newInputWrapperElt = buildNewInputWrapperElt(wrapperElt.getAttribute("data-prototype"), lastKey);

        wrapperElt.appendChild(newInputWrapperElt);

        var lastInput = getLastInput(wrapperElt, lastKey);

        if (placeholder) {
            lastInput.placeholder = placeholder;
        }

        if (hidden) {
            lastInput.parentElement.parentElement.parentElement.style.display = "none";
            lastInput.type = "hidden";

        } else {
            myTrickEditor.addADeleteButton(lastInput.parentElement, lastInput.parentElement.parentElement.parentElement);
        }

        return lastInput;

        /**
         * Build a new input from a prototype
         *
         * @param prototype
         * @param lastKey
         * @returns {Element}
         */
        function buildNewInputWrapperElt(prototype, lastKey) {

            var elt = document.createElement('div');
            elt.innerHTML = prototype.replace(/__name__/g, lastKey);

            return elt.firstElementChild;
        }

        function getLastInput(wrapperElt, lastKey) {

            return wrapperElt.children[lastKey].firstElementChild.firstElementChild.firstElementChild;
        }
    }
};