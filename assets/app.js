//import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

import axios from 'axios';

const dropdownButton = document.getElementById("dropdown-button");
const dropdownMenu = document.getElementById("dropdown-menu");

if(dropdownButton !== null){
    dropdownButton.addEventListener("click", function (event) {
        event.stopPropagation();
        dropdownMenu.classList.toggle("hidden");
        dropdownButton.setAttribute("aria-expanded", dropdownMenu.classList.contains("hidden") ? "false" : "true");
    });
    document.addEventListener("click", function (event) {
        if (!dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add("hidden");
            dropdownButton.setAttribute("aria-expanded", "false");
        }
    });
}


