function doShibbolethLogout(idplogouturl, callback) {
        // First attempt to log out of the local SP
        $.ajax({
                url: '/Shibboleth.sso/Logout',
                success: function(ret) {
                        // Test for full logout
                        splogout = false;
 
                        // Shib 2.3.1
                        // If the SP is not configured correctly you may get a partial logout which would have the message:
                        //      You remain logged into one or more applications accessed during your session. To complete the logout process, please close/exit your browser completely.
                        // If that is the case then you need to comment out the <LogoutInitiator type="SAML2" template="bindingTemplate.html"/> line in /etc/shibboleth/shibboleth2.xml
                        if(
                                (ret.indexOf('You remain logged into one or more applications accessed during your session.') != -1) &&
                                (ret.indexOf('To complete the logout process, please close/exit your browser completely.') != -1)
                        ) {
                                return false;
                        }
 
                        if(ret.indexOf('Logout was successful') != -1)         { splogout = true; } // Shibboleth Version 2.0
                        if(ret.indexOf('Logout completed successfully') != -1) { splogout = true; } // Shibboleth Version 2.3.1
                        if(splogout) {
                                //console.log('splogout success');
                                if(idplogouturl) {
                                        // Next, attempt to log out of the IDP
                                        $.ajax({
                                                url: idplogouturl,
                                                xhrFields: { withCredentials: true },
                                                dataType: 'jsonp',
                                                success: function(ret) {
                                                        //console.log(ret);
                                                        if(ret.result) {
                                                                // Contact with the IDP was successful. Double check logout status
                                                                //console.log('SP Logout: Success. IDP Logout: ' + ret.status);
                                                                $.ajax({
                                                                        url: idplogouturl,
                                                                        data: 'attempt=1',
                                                                        xhrFields: { withCredentials: true },
                                                                        dataType: 'jsonp',
                                                                        success: function(ret) {
                                                                                //console.log(ret);
                                                                                if(ret.result) {
                                                                                        console.log('SP Logout: Success. IDP Logout: Success.');
                                                                                        callback(true);
                                                                                } else {
                                                                                        console.log('SP Logout: Success. IDP Logout: Error while verifying logout: ' + ret.status);
                                                                                        callback(false);
                                                                                }
                                                                        },
                                                                        error: function(ret) {
                                                                                // This will never fire due to a limitation of html/javascript/jquery
                                                                                console.log('SP Logout: Success. IDP Logout: Error while verifying logout: ' + ret);
                                                                                callback(false);
                                                                        }
                                                                });
                                                        } else {
                                                                console.log('SP Logout: Success. IDP Logout: ' + ret.status);
                                                                callback(false);
                                                        }
                                                },
                                                error: function(ret) {
                                                        // This will never fire due to a limitation of html/javascript/jquery
                                                        console.log('SP Logout: Success. IDP Logout: Unable to contact IDP logout URL: ' + idplogouturl);
                                                        callback(false);
                                                }
                                        });
                                } else {
                                        console.log('SP Logout: Success. IDP Logout: No URL specified.');
                                        callback(false);
                                }
                        } else {
                                console.log('Error while attempting to log out of the SP. ' + ret);
                                callback(false);
                        }
                },
                error: function(ret) {
                        console.log('Error while attempting to log out of the SP. ' + ret);
                        callback(false);
                }
        });
}
