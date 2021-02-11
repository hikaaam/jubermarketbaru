import React from 'react';
import ReactDOM from 'react-dom';

import {
    Login
} from './views/index';


if (document.getElementById('example')) {
    ReactDOM.render(<Login />, document.getElementById('example'));
}
