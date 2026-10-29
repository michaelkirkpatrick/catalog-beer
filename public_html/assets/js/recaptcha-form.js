// reCAPTCHA v3 for the guarded forms (create-account, contact).
//
// The form carries data-recaptcha-sitekey and data-recaptcha-action. A token is
// fetched when the page loads and AGAIN at submit time, because tokens expire
// two minutes after they are minted: fetching only on load, as this site did
// until Oct 2026, failed anyone who took longer than that to fill the form. The
// submit waits for the fresh token, then goes through. If Google's script never
// loaded the form submits anyway and the server refuses it.
(function(){
    var form = document.querySelector('form[data-recaptcha-action]');
    if(!form){ return; }
    var field = form.querySelector('input[name="g-recaptcha-response"]');
    var key = form.getAttribute('data-recaptcha-sitekey');
    var action = form.getAttribute('data-recaptcha-action');
    var passing = false;

    function fetchToken(){
        return grecaptcha.execute(key, {action: action}).then(function(token){
            field.value = token;
        });
    }

    if(window.grecaptcha){
        grecaptcha.ready(fetchToken);
    }

    form.addEventListener('submit', function(e){
        if(passing){ return; }
        e.preventDefault();
        var go = function(){ passing = true; form.submit(); };
        if(!window.grecaptcha){ go(); return; }
        grecaptcha.ready(function(){ fetchToken().then(go, go); });
    });
})();
