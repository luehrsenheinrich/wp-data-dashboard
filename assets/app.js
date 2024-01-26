/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

require('bootstrap');
require('datatables.net-bs5');
require('../public/bundles/datatables/js/datatables.js');

import $ from 'jquery';
window.$ = window.jQuery = $;

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
