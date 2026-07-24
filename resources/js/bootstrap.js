import axios from 'axios';
window.axios = axios;

// Thiết lập header mặc định cho mọi request Axios để Laravel nhận diện đây là AJAX request (XMLHttpRequest)
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
