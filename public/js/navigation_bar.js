/**
 * Object to handle a navigation bar
 *
 * @type {{lastScrollY: number, hideOnScroll: navigationBar.hideOnScroll, animatedHideOnScroll: navigationBar.animatedHideOnScroll, element: HTMLElement}}
 */
var navigationBar = {

    element: document.getElementById("navbar"),
    lastScrollY: 0,

    /**
     * Show or hide the navigation bar on scroll
     */
    hideOnScroll: function () {

        var actualScrollY = window.scrollY;

        if (actualScrollY > navigationBar.lastScrollY) {
            navigationBar.element.style.display = "none";
        } else {
            navigationBar.element.style.display = "flex";
        }

        navigationBar.lastScrollY = actualScrollY;
    },

    /**
     * Show or hide the navigation bar on scroll with an animation
     */
    animatedHideOnScroll: function () {

        var actualScrollY = window.scrollY;

        if (actualScrollY > navigationBar.lastScrollY) {
            switchBar(navigationBar.element, true);
        } else {
            switchBar(navigationBar.element, false);
        }

        navigationBar.lastScrollY = actualScrollY;

        /**
         * Show or hide the navigation bar
         * 
         * @param targetElement
         * @param {bool} hide
         */
        function switchBar(targetElement, hide) {

            if (hide) {
                /**
                 * Animation to hide the bar
                 */
                var hideBar = () => {

                    var y = getY(targetElement);

                    if (y.value <= -100) {
                        return;
                    }

                    targetElement.style.top = (y.value - 1) + y.unit;

                    requestAnimationFrame(hideBar);
                };
                requestAnimationFrame(hideBar);

            } else {
                /**
                 * Animation to show the bar
                 */
                var showBar = () => {

                    var y = getY(targetElement);

                    if (y.value >= 0) {
                        return;
                    }

                    targetElement.style.top = (y.value + 1) + y.unit;

                    requestAnimationFrame(showBar);
                };
                requestAnimationFrame(showBar);
            }

            /**
             * Get the CSS top value of an element
             *
             * @param targetElement
             * @returns {{unit: string, value: number}}
             */
            function getY (targetElement) {

                let regex = /^([-]?[0-9]+)([a-z]+)$/,
                    regexResultY = regex.exec(targetElement.style.top || "0px");

                return {
                    value: parseInt(regexResultY[1]),
                    unit: regexResultY[2]
                };
            }
        }
    },
};

