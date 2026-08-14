jQuery(document).ready(function ($) {
    // 1. Media Image Selector Handler
    $('.cosy-media-select-btn').on('click', function (e) {
        e.preventDefault();
        var btn = $(this);
        var targetInput = $(btn.data('target'));
        var previewDiv = $(btn.data('preview'));

        if (typeof wp === 'undefined' || !wp.media) {
            return;
        }

        var mediaFrame = wp.media({
            title: 'Select or Upload Page Image',
            button: { text: 'Use This Image' },
            multiple: false
        });

        mediaFrame.on('select', function () {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            targetInput.val(attachment.url);
            if (previewDiv.length) {
                previewDiv.removeClass('d-none').find('img').attr('src', attachment.url);
            }
        });

        mediaFrame.open();
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // 2. AI Re-index All Profiles Button Handler
    const reindexBtn = document.getElementById('cosy-reindex-ai-btn');
    const reindexStatus = document.getElementById('cosy-reindex-status');
    if (reindexBtn) {
        reindexBtn.addEventListener('click', function () {
            reindexBtn.disabled = true;
            reindexBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Indexing...';
            if (reindexStatus) {
                reindexStatus.className = 'mt-2 small text-info';
                reindexStatus.innerText = 'Connecting to AI API and generating profile vectors...';
            }

            fetch(ajaxurl + '?action=cosy_ai_reindex', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    reindexBtn.disabled = false;
                    reindexBtn.innerHTML = '<i class="fa-solid fa-arrows-rotate me-1"></i> Re-index All Profiles';
                    if (reindexStatus) {
                        if (data.success) {
                            reindexStatus.className = 'mt-2 small text-success';
                            reindexStatus.innerText = '✅ ' + data.data.message;
                        } else {
                            reindexStatus.className = 'mt-2 small text-danger';
                            reindexStatus.innerText = '❌ Error: ' + (data.data ? data.data.message : 'Indexing failed.');
                        }
                    }
                })
                .catch(err => {
                    reindexBtn.disabled = false;
                    reindexBtn.innerHTML = '<i class="fa-solid fa-arrows-rotate me-1"></i> Re-index All Profiles';
                    if (reindexStatus) {
                        reindexStatus.className = 'mt-2 small text-danger';
                        reindexStatus.innerText = '❌ Request failed. Please check network/API key.';
                    }
                });
        });
    }

    // 3. Tab Persistence and Navigation
    var tabButtons = document.querySelectorAll('#v-pills-tab button[data-bs-toggle="pill"]');

    function activateTab(tabTargetId) {
        if (!tabTargetId) return;
        var cleanId = tabTargetId.replace('#', '').replace('v-pills-', '').replace('-tab', '');
        var targetBtn = document.querySelector('#v-pills-' + cleanId + '-tab');
        if (targetBtn) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                var tabObj = bootstrap.Tab.getOrCreateInstance(targetBtn);
                if (tabObj) tabObj.show();
            } else {
                targetBtn.click();
            }
        }
    }

    var urlParams = new URLSearchParams(window.location.search);
    var urlTab = urlParams.get('tab');
    var hashTab = window.location.hash ? window.location.hash.replace('#', '') : '';
    var savedTab = localStorage.getItem('cosy_active_settings_tab');

    var initialTab = urlTab || hashTab || savedTab;
    if (initialTab) {
        activateTab(initialTab);
    }

    tabButtons.forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            var targetId = e.target.getAttribute('data-bs-target').replace('#v-pills-', '');
            localStorage.setItem('cosy_active_settings_tab', targetId);
            if (history.pushState) {
                history.pushState(null, null, '#v-pills-' + targetId);
            } else {
                window.location.hash = '#v-pills-' + targetId;
            }
        });
    });

    var settingsForm = document.querySelector('.cosy-settings-wrap form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function () {
            var activeBtn = document.querySelector('#v-pills-tab button.active');
            if (activeBtn) {
                var targetId = activeBtn.getAttribute('data-bs-target').replace('#v-pills-', '');
                localStorage.setItem('cosy_active_settings_tab', targetId);
            }
        });
    }
});
