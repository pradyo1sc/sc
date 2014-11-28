document_title = document.title;

$(function () {
    $('.signup-form').ajaxForm({
        url: FA_source() + '?t=register',
        
        beforeSend: function() {
            signup_form = $('.signup-form');
            signup_button = signup_form.find('.submit-btn');
            signup_button.attr('disabled', true);
            signup_form.find('.post-message').fadeOut('fast');
            FA_progressIconLoader(signup_button);
        },
        
        success: function(responseText) {
            
            if (responseText.status == 200) {
                window.location = responseText.redirect_url;
            } else {
                signup_button.attr('disabled', false);
                
                if (signup_form.find('.post-message').length == 0) {
                    signup_form
                    .find('.form-header')
                    .after('<div class="post-message hidden">' + responseText.error_message + '</div>')
                    .end().find('.post-message')
                    .fadeIn('fast');
                } else {
                    signup_form.find('.post-message').html(responseText.error_message).fadeIn('fast');
                }
            }
            
            FA_progressIconLoader(signup_button);
        }
    });

    $('.login-form').ajaxForm({
        url: FA_source() + '?t=login',
        
        beforeSend: function() {
            login_form = $('.login-form');
            login_button = login_form.find('.submit-btn');
            login_button.attr('disabled', true);
            login_form.find('.post-message').fadeOut('fast');
            FA_progressIconLoader(login_button);
        },
        
        success: function(responseText) {
            
            if (responseText.status == 200) {
                window.location = responseText.redirect_url;
            } else {
                login_button.attr('disabled', false);
                
                if (login_form.find('.post-message').length == 0) {
                    login_form
                    .find('.form-header')
                    .after('<div class="post-message hidden">' + responseText.error_message + '</div>')
                    .end().find('.post-message')
                    .fadeIn('fast');
                } else {
                    login_form.find('.post-message').html(responseText.error_message).fadeIn('fast');
                }
            }
            
            FA_progressIconLoader(login_button);
        }
    });

    $('.forgotpass-form').ajaxForm({
        url: FA_source() + '?t=forgot_password',
        
        beforeSend: function() {
            forgotpass_form = $('.forgotpass-form');
            forgotpass_button = forgotpass_form.find('.submit-btn');
            forgotpass_button.attr('disabled', true);
            forgotpass_form.find('.post-message').fadeOut('fast');
            FA_progressIconLoader(forgotpass_button);
        },
        
        success: function(responseText) {
            forgotpass_button.attr('disabled', false);
            
            if (forgotpass_form.find('.post-message').length == 0) {
                forgotpass_form
                .find('.form-header')
                .after('<div class="post-message hidden">' + responseText.message + '</div>')
                .end().find('.post-message')
                .fadeIn('fast');
            } else {
                forgotpass_form.find('.post-message').html(responseText.message).fadeIn('fast');
            }
            
            FA_progressIconLoader(forgotpass_button);
        }
    });

    $('.passwordreset-form').ajaxForm({
        url: FA_source() + '?t=reset_password',
        
        beforeSend: function() {
            passwordreset_form = $('.passwordreset-form');
            passwordreset_button = passwordreset_form.find('.submit-btn');
            passwordreset_button.attr('disabled', true);
            FA_progressIconLoader(passwordreset_button);
        },
        
        success: function(responseText) {
            
            if (responseText.status == 200) {
                passwordreset_form.find('.form-header').after('<div class="post-message hidden">Successful! Please log in with your new password.</div>');
                passwordreset_form.find('.post-message').fadeIn('fast',function () {
                    $(this).fadeOut(4000, function() {
                        $(this).remove();
                        window.location = responseText.url;
                    });
                });
            }
            else {
                passwordreset_button.attr('disabled', false);
                
                passwordreset_form.find('.form-header').after('<div class="post-message hidden">Something went wrong! Please try again.</div>');
                passwordreset_form.find('.post-message').fadeIn('fast',function () {
                    $(this).fadeOut(4000, function() {
                        $(this).remove();
                    });
                });
            }
            
            FA_progressIconLoader(passwordreset_button);
        }
    });
    
    $(document).on('focusin', '*[data-placeholder]', function() {
        elem = $(this);
        
        if (elem.val() == elem.attr('data-placeholder')) {
            elem.val('');
        }
    });
    
    $(document).on('focusout', '*[data-placeholder]', function() {
        elem = $(this);
        
        if (elem.val().length == 0) {
            elem.val(elem.attr('data-placeholder'));
        }
    });
    
    $(document).on('keyup', '*[data-copy-to]', function() {
        elem = $(this);
        elem_val = elem.val();
        elem_placeholder = elem.attr('data-placeholder');
        
        if (elem_val == elem_placeholder) {
            $(elem.attr('data-copy-to')).val('');
        }
        else {
            $(elem.attr('data-copy-to')).val(elem_val);
        }
    });
    
    $(document).on('keyup', '.auto-grow-input', function() {
        elem = $(this);
        initialHeight = '10px';
        
        if (elem.attr('data-height')) {
            initialHeight = elem.attr('data-height') + 'px';
        }
        
        this.style.height = initialHeight;
        this.style.height = (this.scrollHeight) + 'px';
    });
});

// Header search
function FA_headerSearch(query) {
    search_wrapper = $('.dropdown-search-container');
    
    if (query.length == 0) {
        search_wrapper.hide();
    }
    else {
        search_wrapper.show();
        FA_progressIconLoader(search_wrapper.find('.search-header'));
        
        $.get(FA_source(), {t: 'search', a: 'header', q: query}, function (data) {
            
            if (data.status == 200) {
                
                if (data.html.length == 0) {
                    search_wrapper
                    .find('.search-list-wrapper')
                        .html('<div class="no-wrapper">No result found!</div>')
                    .end().find('a.page-link')
                            .hide();
                } else {
                    search_wrapper
                        .find('.search-list-wrapper')
                            .html(data.html)
                        .end()
                        .find('a.page-link')
                            .attr('href', data.link).show();
                }
            }
            
            FA_progressIconLoader(search_wrapper.find('.search-header'));
        });
    }
}

// Progress Icon Loader
function FA_progressIconLoader(container_elem) {
    progress_icon_elem = container_elem.find('i.progress-icon');
    default_icon = progress_icon_elem.attr('data-icon');
    
    hide_back = false;
    
    if (progress_icon_elem.hasClass('hide') == true) {
        hide_back = true;
    }
    
    if (container_elem.find('i.icon-spinner').length == 1) {
        progress_icon_elem
            .removeClass('icon-spinner')
            .removeClass('icon-spin')
            .addClass('icon-' + default_icon);
        if (hide_back == true) {
            progress_icon_elem.hide();
        }
    }
    else {
        progress_icon_elem
            .removeClass('icon-'+default_icon)
            .addClass('icon-spinner icon-spin')
            .show();
    }
    return true;
}

// Generate username
function FA_generateUsername(query) {
    var username = query.replace(/[^A-Za-z0-9_\-\.]/ig, '').toLowerCase();
    $('.register-username-textinput').val(username).keyup();
}

// Check username
function FA_checkUsername(query,timeline_id,target,detailed) {
    target = $(target);
    target_html = '';
    
    $.get(FA_source(), {t: 'username', a: 'check', q: query, timeline_id: timeline_id}, function(data) {
        
        if (data.status == 200) {
            
            if (detailed == true) {
                target_html = '<span style="color: #94ce8c;"><i class="icon-ok"></i> Username available!</span>';
            } else {
                target_html = '<span style="color: #94ce8c;"><i class="icon-ok"></i></span>';
            }
        } else if (data.status == 201) {
            
            if (detailed == true) {
                target_html = '<span style="color: #94ce8c;">This is you!</span>';
            } else {
                target_html = '<span style="color: #94ce8c;"></span>';
            }
        } else if (data.status == 410) {
            
            if (detailed == true) {
                target_html = '<span style="color: #ee2a33;"><i class="icon-remove"></i> Username not available!</span>';
            } else {
                target_html = '<span style="color: #ee2a33;"><i class="icon-remove"></i></span>';
            }
        } else if (data.status == 406) {
            
            if (detailed == true) {
                target_html = '<span style="color: #ee2a33;"><i class="icon-remove"></i> Username should atleast be 4 characters, cannot be only numbers, can contain alphabets [A-Z], numbers [0-9] and underscores (_) only.</span>';
            } else {
                target_html = '<span style="color: #ee2a33;"><i class="icon-remove"></i></span>';
            }
        }
        
        if (target_html.length == 0) {
            target.html('').hide();
        } else {
            target.html(target_html).show();
        }
    });
}