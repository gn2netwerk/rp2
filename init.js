jQuery(document).ready(function(){

    var keepPolling = true;

    var poll = function(){
        
        if (!keepPolling) {
            return true;
        }
        
        jQuery.post('?', { 'a':'1', 'prefix':resultPrefix }, function(data){
            
            var results = jQuery('#results');
            var domains = jQuery('#domains');
            var customers = jQuery('#customers');
            
            domains.html('');
            customers.html('');
            
            jQuery.each(data, function(i, serverResult) {
                if (serverResult.domains) {
                    jQuery.each(serverResult.domains, function(j, domain) {
                        domains = jQuery('#domains');
                        if (domains.size() == 0) {
                            results.append('<div id="domains"></div>');
                        }
                        var domainResult = jQuery('<div class="result"></div>');
                        domainResult.append('<div class="server"><a href="'+domain.server+'">'+domain.server+'</a></div>');
                        domainResult.append('<div class="domain">'+domain.domain+'<form action="'+domain.server+'" method="post"><input type="hidden" value="auth" name="_login[action]"><input type="hidden" name="_login[user]" value="'+domain.user+':'+domain.domain+'"><input type="hidden" name="_login[pass]" value="'+domain.pass+'"><input type="submit" value="anmelden"></form></div>');
                        domains.append(domainResult);
                    });
                }
                if (serverResult.customers) {
                    jQuery.each(serverResult.customers, function(j, customer) {
                        customers = jQuery('#customers');
                        if (customers.size() == 0) {
                            results.append('<div id="customers"></div>');
                        }
                        var customerResult = jQuery('<div class="result"></div>');
                        customerResult.append('<div class="server"><a href="'+customer.server+'">'+customer.server+'</a></div>');
                        customerResult.append('<div class="customer">'+customer.title+' '+customer.first_name+' '+customer.last_name+'<br>'+customer.zip+' '+customer.city+'<br>Comment: '+customer.comment+'</div>');
                        customers.append(customerResult);
                    });
                }
            });
        }, 'json');//, 'json'
        setTimeout(poll, 500);
    };

    poll();
    
    setTimeout(function(){ keepPolling=false; },10000);
    
});