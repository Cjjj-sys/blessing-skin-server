/* eslint-disable no-var */
import 'core-js/fn/array/includes';
import 'core-js/fn/array/find';
import 'core-js/fn/object/assign';
import 'core-js/fn/object/values';
import 'core-js/fn/string/starts-with';
import 'es6-promise/auto';
import 'whatwg-fetch';

Number.parseInt = parseInt;

document.body.classList.replace = function (oldToken, newToken) {
    var list = document.body.classList;
    list.remove(oldToken);
    list.add(newToken);
};
