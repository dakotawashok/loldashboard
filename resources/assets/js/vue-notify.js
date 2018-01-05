import 'toastr/build/toastr.css'
import toastr from 'toastr/build/toastr.min.js'

toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    // "positionClass": "toast-bottom-center",
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
}

export default {
    install(Vue, options){
        Vue.prototype.$notify = {}

        Vue.prototype.$notify.error = function(message){
            if(message == null){
                message = 'Error!'
            }
            toastr.error(message)
        }

        Vue.prototype.$notify.warning = function(message){
            if(message == null){
                message = 'Oops!'
            }
            toastr.warning(message)
        }

        Vue.prototype.$notify.success = function(message){
            if(message == null){
                message = 'Success.'
            }
            toastr.success(message)
        }

        Vue.prototype.$notify.info = function(message){
            if(message == null){
                message = 'Info.'
            }
            toastr.info(message)
        }

    }
}
