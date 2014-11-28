document_title = document.title;
current_notif_count = 0;
current_msg_count = 0;
current_followreq_count = 0;

$(function () {
    setInterval(function () {
        SK_intervalUpdates();
    }, 10000);
    
    if ($('.chat-wrapper').length == 1) {
        $('.chat-messages').scrollTop($(this).prop('scrollHeight'));
    }
    
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

// Interval Updates
function SK_intervalUpdates() {
    
    $.get(SK_source(), {t: 'interval'}, function (data) {
        
        // Get new notifications
        if (typeof(data.notifications) != "undefined" && data.notifications > 0) {
            $('.notification-nav').find('.new-update-alert').text(data.notifications).show();

            if (data.notifications != current_notif_count) {
                document.getElementById('notification-sound').play();
                current_notif_count = data.notifications;
            }
        }
        else {
            $('.notification-nav').find('.new-update-alert').hide();
            current_notif_count = 0;
        }
        
        // Get new messages
        if (typeof(data.messages) != "undefined" && data.messages > 0) {
            $('.message-nav').find('.new-update-alert').text(data.messages).show();
            
            if ($('.online-header').length == 1) {
                SK_getOnlineList('');
                $('.online-header').find('.update-alert').show();
            }
            
            if ($('.chat-wrapper').length == 1) {
                loadNewChatMessages();
            }

            if (data.messages != current_msg_count) {
                document.getElementById('notification-sound').play();
                current_msg_count = data.messages;
            }
        } else {
            $('.message-nav').find('.new-update-alert').hide();
            
            if ($('.online-header').length == 1) {
                $('.online-header').find('.update-alert').hide();
            }

            current_msg_count = 0;
        }
        
        // Get new follow requests
        if (typeof(data.follow_requests) != "undefined" && data.follow_requests > 0) {
            $('.followers-nav')
                .attr('href', $('.followers-nav').attr('href').replace('following', 'requests'))
                .find('.new-update-alert').text(data.follow_requests).show();

            if (data.follow_requests != current_followreq_count) {
                document.getElementById('notification-sound').play();
                current_followreq_count = data.follow_requests;
            }
        } else {
            $('.followers-nav')
                .find('.new-update-alert').hide();
            current_followreq_count = 0;
        }
    });
}

// Follow
function SK_registerFollow(id) {
    element = $('.follow-' + id);
    
    SK_progressIconLoader(element);
    
    $.post(SK_source() + '?t=follow&a=follow', {following_id: id}, function (data) {
        
        if (data.status == 200) {
            element.after(data.html);
            element.remove();
        }
    });
}

// Filter stories
function SK_filterStories(type, timeline_id) {
    main_wrapper = $('.story-filters-wrapper');
    filter_wrapper = main_wrapper.find('.' + type + '-wrapper');
    stories_wrapper = $('.stories-container');
    
    SK_progressIconLoader(filter_wrapper);
    
    sendData = new Object();
    sendData.t = 'post';
    sendData.a = 'filter';
    sendData.type = type;
    
    if (typeof(timeline_id) != "undefined") {
        sendData.timeline_id = timeline_id;
        stories_wrapper.attr('data-story-timeline', timeline_id);
    }
    
    stories_wrapper.attr('data-story-type', type)
        .find('.stories-wrapper').html('')
        .end()
        .find('.load-btn').fadeOut('fast');
    
    $.get(SK_source(), sendData, function (data) {
        
        if (data.status == 200) {
            stories_wrapper
                .find('.stories-wrapper')
                    .html(data.html)
                .end()
                .find('.load-btn')
                    .fadeIn('fast').attr('onclick','SK_loadOldStories();').html('<i class="icon-reorder progress-icon hide"></i> View previous posts');
        }
        
        main_wrapper.find('.filter-active').removeClass('filter-active');
        filter_wrapper.addClass('filter-active');
        
        SK_progressIconLoader(filter_wrapper);
        
    });
}

// Like story
function SK_registerStoryLike(post_id) {
    main_elem = $(".story_" + post_id);
    like_btn = main_elem.find('.story-like-btn');
    like_activity_btn = main_elem.find('.story-like-activity');
    
    SK_progressIconLoader(like_btn);
    
    $.get(SK_source(), {t: 'post', post_id: post_id, a: 'like'}, function(data) {
        if (data.status == 200) {
            if (data.liked == true) {
                like_btn
                    .after(data.button_html)
                    .remove();
                like_activity_btn
                    .html(data.activity_html);
            } else {
                like_btn
                    .after(data.button_html)
                    .remove();
                like_activity_btn
                    .html(data.activity_html);
            }
        }
    });
}

// Share story
function SK_registerStoryShare(post_id) {
    main_elem = $('.story_'+post_id);
    share_btn = main_elem.find('.story-share-btn');
    share_activity_btn = main_elem.find('.story-share-activity');
    
    SK_progressIconLoader(share_btn);
    
    $.get(SK_source(), {t: 'post', post_id: post_id, a: 'share'}, function(data) {
        if (data.status == 200) {
            if (data.shared == true) {
                share_btn
                    .after(data.button_html)
                    .remove();
                share_activity_btn
                    .html(data.activity_html);
            } else {
                share_btn
                    .after(data.button_html)
                    .remove();
                share_activity_btn
                    .html(data.activity_html);
            }
        }
    });
}

// Follow story
function SK_registerStoryFollow(post_id) {
    main_elem = $('.story_'+post_id);
    follow_btn = main_elem.find('.story-follow-btn');
    follow_activity_btn = main_elem.find('.story-follow-activity');
    
    SK_progressIconLoader(follow_btn);
    
    $.get(SK_source(), {t: 'post', post_id: post_id, a: 'follow'}, function(data) {
        if (data.status == 200) {
            if (data.shared == true) {
                follow_btn
                    .after(data.button_html)
                    .remove();
                follow_activity_btn
                    .html(data.activity_html);
            } else {
                follow_btn
                    .after(data.button_html)
                    .remove();
                follow_activity_btn
                    .html(data.activity_html);
            }
        }
    });
}

// Like comment
function SK_registerCommentLike(post_id) {
    main_elem = $('.comment_' + post_id);
    like_btn = main_elem.find('.comment-like-btn');
    like_activity_btn = main_elem.find('.comment-like-activity');
    
    SK_progressIconLoader(like_btn);
    
    $.get(SK_source(), {t: 'post', post_id: post_id, a: 'like'}, function(data) {
        if (data.status == 200) {
            if (data.liked == true) {
                like_btn
                    .after(data.button_html)
                    .remove();
                like_activity_btn
                    .html(data.activity_html);
            } else {
                like_btn
                    .after(data.button_html)
                    .remove();
                like_activity_btn
                    .html(data.activity_html);
            }
        }
    });
}

// Show post likes window (popup)
function SK_getStoryLikes(post_id) {
    main_elem = $('.story_' + post_id);
    like_activity_btn = main_elem.find('.like-activity');
    SK_progressIconLoader(like_activity_btn);
    
    $.get(SK_source(), {t: 'post', post_id: post_id, a: 'like_window'}, function(data) {
        
        if (data.status == 200) {
            $(document.body)
                .append(data.html)
                .css('overflow','hidden');
            
            if ($('.header-wrapper').width() < 920) {
                $('.window-wrapper').css('margin-top',($(document).scrollTop()+10)+'px');
            }
        }
        
        SK_progressIconLoader(like_activity_btn);
    });
}

// Show post shares window
function SK_getStoryShares(post_id) {
    main_elem = $('.story_' + post_id);
    share_activity_btn = main_elem.find('.share-activity');
    SK_progressIconLoader(share_activity_btn);
    
    $.get(SK_source(), {t: 'post', post_id: post_id, a: 'share_window'}, function(data) {
        
        if (data.status == 200) {
            $(document.body)
                .append(data.html)
                .css('overflow','hidden');
            
            if ($('.header-wrapper').width() < 920) {
                $('.window-wrapper').css('margin-top',($(document).scrollTop()+10)+'px');
            }
        }
        
        SK_progressIconLoader(share_activity_btn);
    });
}

// Show comment likes window (popup)
function SK_getCommentLikes(comment_id) {
    main_elem = $('.comment_' + comment_id);
    like_activity_btn = main_elem.find('.comment-like-activity');
    SK_progressIconLoader(like_activity_btn);
    
    $.get(SK_source(), {t: 'post', post_id: comment_id, a: 'like_window'}, function(data) {
        
        if (data.status == 200) {
            $(document.body)
                .append(data.html)
                .css('overflow','hidden');
            
            if ($('.header-wrapper').width() < 920) {
                $('.window-wrapper').css('margin-top',($(document).scrollTop()+10)+'px');
            }
        }
        
        SK_progressIconLoader(like_activity_btn);
    });
}

// Show delete post window
function SK_deletePostWindow(post_id) {
    if ($('.story_' + post_id).length == 1) {
        main_wrapper = $('.story_' + post_id);
        button_wrapper = main_wrapper.find('.remove-btn');
    } else {
        main_wrapper = $('.comment_' + post_id);
        button_wrapper = main_wrapper.find('.comment-remove-btn');
    }
    
    SK_progressIconLoader(button_wrapper);
    
    $.get(SK_source(), {t: 'post', post_id: post_id, a: 'delete_window'}, function(data) {
        
        if (data.status == 200) {
            $(document.body)
                .append(data.html)
                .css('overflow','hidden');
            
            if ($('.header-wrapper').width() < 920) {
                $('.window-wrapper').css('margin-top',($(document).scrollTop()+10)+'px');
            }
        }
        
        SK_progressIconLoader(button_wrapper);
    });
}

// Delete post
function SK_deletePost(post_id) {
    SK_closeWindow();
    $.get(SK_source(), {t: 'post', post_id: post_id, a: 'delete'}, function(data) {
        
        if (data.status == 200) {
            
            if (data.post_type == "story") {
                $('.story_' + post_id).slideUp(function(){
                    $(this).remove();
                });

                $('.photo_' + post_id).fadeOut(function(){
                    $(this).remove();
                });
            } else if (data.post_type == "comment") {
                $('.comment_' + post_id).slideUp(function(){
                    $(this).remove();
                });
            }
        }
    });
}
function SK_cancelDeleteWindow(post_id) {
    if ($('.story_' + post_id).length == 1) {
        main_wrapper = $('.story_' + post_id);
    } else {
        main_wrapper = $('.comment_' + post_id);
    }
    
    button_wrapper = main_wrapper.find('.remove-btn');
    SK_progressIconLoader(button_wrapper);
    SK_closeWindow();
}

// Report Post
function SK_reportPost(post_id) {
    
    if ( $('.story_' + post_id).length == 1) {
        main_wrapper = $('.story_' + post_id);
    }
    else
    if ( $('.comment_' + post_id).length == 1) {
        main_wrapper = $('.comment_' + post_id);
    }
    else {
        return false;
    }
    
    SK_progressIconLoader(main_wrapper.find('.report-btn'));
    
    $.get (SK_source(), {t: 'post', post_id: post_id, a: 'report'}, function(data) {
        
        if (data.status == 200) {
            
            main_wrapper.find('.report-btn').text('Reported!').fadeOut(1500);
        }
        
        SK_progressIconLoader($('.story_' + post_id).find('.report-btn'));
    });
}

// Post comment
function SK_registerComment(text, post_id, timeline_id, event) {
    if (event.keyCode == 13 && event.shiftKey == 0) {
        main_wrapper = $('.story_' + post_id);
        comment_textarea = main_wrapper.find('.comment-textarea');
        textarea_wrapper = comment_textarea.find('textarea');
        textarea_wrapper.val('');
        
        SK_progressIconLoader(comment_textarea);
        
        $.post(SK_source() + '?t=post&a=comment&post_id=' + post_id, {text: text, timeline_id: timeline_id}, function (data) {
            
            if (data.status == 200) {
                main_wrapper.find('.comment-wrapper:last').before(data.html);
                main_wrapper.find('.story-comment-activity').html(data.activity_html);
            }
            
            SK_progressIconLoader(comment_textarea);
        });
    }
}

// Load more comments
function SK_loadAllComments(post_id) {
    main_wrapper = $('.story_' + post_id);
    view_more_wrapper = main_wrapper.find('.view-more-wrapper');
    
    SK_progressIconLoader(view_more_wrapper);
    
    $.get(SK_source(), {t: 'post', a: 'load_all_comments', post_id: post_id}, function (data) {
        
        if (data.status == 200) {
            main_wrapper.find('.comments-wrapper').html(data.html);
            view_more_wrapper.remove();
        }
    });
}

// Load old stories
function SK_loadOldStories() {
    body_wrapper = $('.stories-container');
    button_wrapper = $('.stories-container').find('.load-btn');
    
    SK_progressIconLoader(button_wrapper);
    
    outgoing_data = new Object();
    outgoing_data.t = 'post';
    outgoing_data.a = 'filter';
    
    if ( typeof(body_wrapper.attr('data-story-type')) == "string" ) {
        outgoing_data.type = body_wrapper.attr('data-story-type');
    }
    
    if ( typeof(body_wrapper.attr('data-story-timeline')) =="string" ) {
        outgoing_data.timeline_id = body_wrapper.attr('data-story-timeline');
    }
    
    if ($('.story-wrapper').length > 0) {
        outgoing_data.after_id = $('.story-wrapper:last').attr('data-story-id');
    }
    
    $.get(SK_source(), outgoing_data, function (data) {
        
        if (data.status == 200 ) {
            $('.stories-wrapper').append(data.html);
        } else {
            button_wrapper.text('No more posts to show').removeAttr('onclick');
        }
        
        SK_progressIconLoader(button_wrapper);
        
    });
}

/* Lightbox */
function SK_openLightbox(post_id) {
    if ($(".header-wrapper").width() < 960) {
        window.location = 'index.php?tab1=story&id=' + post_id;
    } else {
        $(".sc-lightbox-container").remove();
        $(document.body).append('<div class="pre_load_wrap"><div class="bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div></div>');

        $.get(SK_source(), {t: 'post', a: 'lightbox', post_id: post_id}, function (data) {

            if (data.status == 200) {
                $(document.body).append(data.html);
            } else {
                $('.pre_load_wrap').remove();
            }
        });
    }
}

// Open chat
function SK_getChat(recipient_id, recipient_name) {
    chat_container = $('.chat-container');
    
    if (chat_container.length == 1) {
    
        if ($('.header-wrapper').width() < 960) {
            startPageLoadingBar();
            SK_loadPage('?tab1=messages&recipient_id=' + recipient_id);
        } else {
            $(document.body).attr('data-chat-recipient', recipient_id);
            $('.chat-recipient-name').text(recipient_name);
            $('.chat-wrapper').show();
            
            $.get(SK_source(), {t: 'chat', a: 'load_messages', recipient_id: recipient_id} ,function (data) {
                
                if (data.status == 200) {
                    $('.chat-wrapper').remove();
                    $('.chat-container').prepend(data.html);
                    $('.chat-wrapper').show();
                    $('.chat-textarea textarea').keyup();
                    $('#online_' + recipient_id)
                        .find('.update-alert').hide();
                    SK_intervalUpdates();
                }
                
                setTimeout(function() {
                $('.chat-messages').scrollTop($('.chat-messages').prop('scrollHeight'));
                }, 500);
            });
        }
    } else {
        startPageLoadingBar();
        SK_loadPage('?tab1=messages&recipient_id=' + recipient_id);
    }
}

// Close popup window
function SK_closeWindow() {
    $('.window-container').remove();
    $(document.body).css('overflow','auto');
}

// Progress Icon Loader
function SK_progressIconLoader(container_elem) {
    container_elem.each(function() {
        progress_icon_elem = $(this).find('i.progress-icon');
        default_icon = progress_icon_elem.attr('data-icon');
        
        hide_back = false;
        
        if (progress_icon_elem.hasClass('hide') == true) {
            hide_back = true;
        }
        
        if ($(this).find('i.icon-spinner').length == 1) {
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
    });
}

// Generate username
function SK_generateUsername(query) {
    var username = query.replace(/[^A-Za-z0-9_\-\.]/ig, '').toLowerCase();
    $('.register-username-textinput').val(username).keyup();
}

// Check username
function SK_checkUsername(query,timeline_id,target,detailed) {
    target = $(target);
    target_html = '';
    
    $.get(SK_source(), {t: 'username', a: 'check', q: query, timeline_id: timeline_id}, function(data) {
        
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

function addEmoToInput(code,input) {
    inputTag = $(input);
    inputVal = inputTag.val();
    
    if (typeof(inputTag.attr('placeholder')) != "undefined") {
        inputPlaceholder = inputTag.attr('placeholder');
        
        if (inputPlaceholder == inputVal) {
            inputTag.val('');
            inputVal = inputTag.val();
        }
        
    }
    
    if (inputVal.length == 0) {
        inputTag.val(code + ' ');
    } else {
        inputTag.val(inputVal + ' ' + code);
    }
    
    inputTag.keyup();
}