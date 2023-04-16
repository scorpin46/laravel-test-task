(function ($){
    let UI = {
        $document: $(document),
        $window: $(window),
        $ajaxLoader: $('#ajax-loader'),

        init: () => {
            window.csrf = $('head meta[name="csrf-token"]').attr('content');

            UI.initSearchInput();
            UI.initLazyLoad();
            UI.initCollapsedBlocks();
            UI.initArticleAjaxSaving();
        },

        initSearchInput: () => {
            $('#search-input').on('input', function(){
                let query = this.value.trim();

                if (window.searchXhr){
                    window.searchXhr.abort();
                }

                window.searchXhr = $.ajax({
                    url: location.href,
                    method: 'POST',
                    data: {
                        searchQuery: query,
                        _token: window.csrf,
                    },
                    error: function(response, errorType){
                        if (errorType !== 'abort'){
                            $('.articles__list').html('Ошибка запроса...');
                        }
                    }
                }).done(function (data) {
                    $('.articles__list').hide().html(data).fadeIn(200);
                })
            })
        },

        initLazyLoad: () => {
            $('[data-lazy-load]').each(function () {
                let $self = $(this);
                let itemSelector = $self.data('lazy-load');
                let $lastItem = $self.find(itemSelector).last();

                if (!$self.find(itemSelector).length) {
                    return false;
                }

                let page = 2;
                let inProgress = false;

                $(window).on('scroll', function () {
                    if (!inProgress && $(this).scrollTop() > $lastItem.offset().top - window.screen.height) {

                        $.ajax({
                            url: location.href,
                            method: 'POST',
                            data: {
                                page: page,
                                searchQuery: $('#search-input').val().trim(),
                                _token: window.csrf,
                            },
                            beforeSend: function () {
                                inProgress = true;
                            }
                        }).done(function (data) {
                            let delay = 700;
                            let $items = $("<div></div>").html(data).find(itemSelector).hide();

                            if ($items.length) {
                                $lastItem.after($items);
                                $items.fadeIn(delay);

                                $lastItem = $items.last();

                                inProgress = false;
                                page++;
                            }
                        });
                    }
                });
            });
        },

        initCollapsedBlocks: () => {
            let $hashTab = $('[data-collapse][data-target="' + location.hash + '"]');

            if ($hashTab.length) {
                $hashTab.parents('.collapse-block').each(function(){
                    $('[data-collapse][data-target="#' + $(this).attr('id') + '"]').attr('data-collapse', false).siblings().attr('data-collapse', true);
                });
                $hashTab.attr('data-collapse', false).siblings().attr('data-collapse', true);
                window.scrollTo(0, 0);
            }

            $('[data-collapse]').each(function () {
                let $this = $(this);
                let $target = $($this.data('target'));
                let isTargetCollapsed = !!$.parseJSON($this.data('collapse'));

                if ($target.length) {
                    if (isTargetCollapsed) {
                        $target.addClass('collapsed');
                    }
                    else {
                        $target.removeClass('collapsed');
                    }

                    if ($this.hasClass('js-tab')
                        && isTargetCollapsed
                        && $this.index() === $this.siblings().length /*is last*/
                        && $this.siblings('.js-tab[data-collapse="false"]').length === 0
                    ){
                        let $firstTab = $this.siblings('.js-tab').eq(0);
                        $firstTab.attr('data-collapse', false);
                        $($firstTab.data('target')).removeClass('collapsed');
                    }
                }
            });

            UI.$document.on('click', '[data-collapse], .collapse', function (e) {
                let $this = $(this);
                let $target = $($this.attr('data-target'));

                if ($target.length) {
                    e.preventDefault();

                    let collapse = $this.attr('data-collapse') || (!$target.is(':visible')).toString();
                    let collapseValue = !!$.parseJSON(collapse);
                    let curTitle = $this.find('.toggle-title').html();
                    let newTitle = $this.attr('data-toggle-title');

                    if (curTitle && newTitle) {
                        $this.find('.toggle-title').html(newTitle);
                        $this.attr('data-toggle-title', curTitle);
                    }

                    if ($this.hasClass('js-tab')) {
                        $target.removeClass('collapsed').siblings().addClass('collapsed');
                        $this.attr('data-collapse', false).siblings().attr('data-collapse', true);

                        if (history) {
                            history.pushState(window.url('?'), null, location.pathname + $this.attr('data-target'));
                        }
                    }
                    else {
                        $target.toggleClass('collapsed');
                        $this.attr('data-collapse', !collapseValue);
                    }
                }
            });
        },

        initArticleAjaxSaving: () => {

            $('#article-edit-form').on('submit', function(e){
                if ($(e.originalEvent.submitter).attr('name') === 'save-ajax'){
                    e.preventDefault();

                    $.ajax({
                        url: location.href,
                        method: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        beforeSend: () => {
                            UI.$ajaxLoader.show();
                        }
                    }).done(function(response){
                        $('html,body').animate({scrollTop: 0});

                        if (response.success){
                            $('#success-msg').removeClass('hidden');
                        }

                        if (response.errors.length){
                            let $ul = $('#error-msg').removeClass('hidden').find('ul').empty();

                            Object.values(response.errors).forEach(err => {
                                $ul.append(`<li>${err}</li>`);
                            });
                        }

                        UI.$ajaxLoader.hide();
                    });
                }
            })
        }
    }

    UI.init();
})(jQuery)