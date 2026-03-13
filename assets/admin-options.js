(function() {
    function clone(value) {
        return JSON.parse(JSON.stringify(value || {}));
    }

    function getConfig() {
        return window.edwAdminOptionsData || {};
    }

    function getForm() {
        return document.querySelector('.edw-options-form');
    }

    function getHiddenInput() {
        var form = getForm();
        return form ? form.querySelector('.edw-location-rules-input') : null;
    }

    function getLocationState() {
        var config = getConfig();
        return clone(config.locationRules);
    }

    function setLocationState(state) {
        var config = getConfig();
        config.locationRules = clone(state);

        var input = getHiddenInput();
        if (input) {
            input.value = JSON.stringify(config.locationRules);
        }
    }

    function stateLabel(country, state) {
        var config = getConfig();
        if (state === 'default') {
            return config.strings.allStates;
        }

        return state + ' - ' + (((config.states || {})[country] || {})[state] || '');
    }

    function countryLabel(country) {
        var config = getConfig();
        return country + ' - ' + ((config.countries || {})[country] || '');
    }

    function ensureCountryWrapper(list, country) {
        var wrapper = list.querySelector('.edw-location-country[data-country="' + country + '"]');
        if (wrapper) {
            return wrapper;
        }

        wrapper = document.createElement('div');
        wrapper.className = 'border rounded-lg p-4 bg-gray-50 edw-location-country';
        wrapper.setAttribute('data-country', country);

        var title = document.createElement('h3');
        title.className = 'font-bold text-lg mb-2';
        title.textContent = countryLabel(country);
        wrapper.appendChild(title);

        list.appendChild(wrapper);
        return wrapper;
    }

    function createRuleNode(country, state, rule) {
        var config = getConfig();
        var row = document.createElement('div');
        row.className = 'mb-3 p-4 bg-white rounded shadow-sm flex flex-col md:flex-row items-center gap-4 edw-location-rule';
        row.setAttribute('data-country', country);
        row.setAttribute('data-state', state);

        var labelWrap = document.createElement('div');
        labelWrap.className = 'flex-1';
        var label = document.createElement('span');
        label.className = 'font-semibold text-sm';
        label.textContent = stateLabel(country, state);
        labelWrap.appendChild(label);

        var controls = document.createElement('div');
        controls.className = 'flex flex-col md:flex-row gap-2';

        var daysLabel = document.createElement('label');
        daysLabel.className = 'flex items-center gap-2';
        daysLabel.innerHTML = '<span>' + config.strings.days + '</span>';
        var daysInput = document.createElement('input');
        daysInput.type = 'number';
        daysInput.min = '0';
        daysInput.max = '999';
        daysInput.className = 'input input-sm input-bordered w-20 edw-rule-days';
        daysInput.value = Number(rule.days || 0);
        daysLabel.appendChild(daysInput);

        var maxDaysLabel = document.createElement('label');
        maxDaysLabel.className = 'flex items-center gap-2';
        maxDaysLabel.innerHTML = '<span>' + config.strings.maxDays + '</span>';
        var maxDaysInput = document.createElement('input');
        maxDaysInput.type = 'number';
        maxDaysInput.min = '0';
        maxDaysInput.max = '999';
        maxDaysInput.className = 'input input-sm input-bordered w-24 edw-rule-max-days';
        maxDaysInput.value = Number(rule.max_days || 0);
        maxDaysLabel.appendChild(maxDaysInput);

        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'text-red-600 hover:underline edw-remove-location-rule';
        removeButton.textContent = config.strings.remove;

        controls.appendChild(daysLabel);
        controls.appendChild(maxDaysLabel);
        controls.appendChild(removeButton);

        row.appendChild(labelWrap);
        row.appendChild(controls);
        return row;
    }

    function renderLocationRules() {
        var list = document.querySelector('.edw-location-rules-list');
        if (!list) {
            return;
        }

        var state = getLocationState();
        list.innerHTML = '';

        Object.keys(state).sort().forEach(function(country) {
            var countryWrapper = ensureCountryWrapper(list, country);
            Object.keys(state[country]).sort().forEach(function(region) {
                countryWrapper.appendChild(createRuleNode(country, region, state[country][region]));
            });
        });
    }

    function populateStates(country) {
        var config = getConfig();
        var statesSelect = document.querySelector('.edw-state-select');
        if (!statesSelect) {
            return;
        }

        statesSelect.innerHTML = '';

        var defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = config.strings.selectState;
        statesSelect.appendChild(defaultOption);

        Object.entries((config.states || {})[country] || {}).forEach(function(entry) {
            var option = document.createElement('option');
            option.value = entry[0];
            option.textContent = entry[1];
            statesSelect.appendChild(option);
        });
    }

    function syncCustomMessageVisibility() {
        var modeMessage = document.querySelector("select[name='_edw_mode']");
        var customMessageInput = document.querySelector('.edw_block_custom_message');

        if (!modeMessage || !customMessageInput) {
            return;
        }

        customMessageInput.style.display = modeMessage.value === '3' ? 'block' : 'none';
    }

    function addLocationRule() {
        var countrySelect = document.querySelector('.edw-country-select');
        var stateSelect = document.querySelector('.edw-state-select');
        if (!countrySelect) {
            return;
        }

        var country = (countrySelect.value || '').toUpperCase().trim();
        var state = stateSelect ? ((stateSelect.value || '').toUpperCase().trim() || 'default') : 'default';
        if (!country) {
            return;
        }

        var currentState = getLocationState();
        if (!currentState[country]) {
            currentState[country] = {};
        }

        if (!currentState[country][state]) {
            currentState[country][state] = { days: 2, max_days: 5 };
        }

        setLocationState(currentState);
        renderLocationRules();

        countrySelect.value = '';
        if (stateSelect) {
            stateSelect.innerHTML = '';
            var defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = getConfig().strings.selectState;
            stateSelect.appendChild(defaultOption);
        }
    }

    function removeLocationRule(button) {
        var row = button.closest('.edw-location-rule');
        if (!row) {
            return;
        }

        var country = row.getAttribute('data-country');
        var state = row.getAttribute('data-state');
        var currentState = getLocationState();

        if (currentState[country]) {
            delete currentState[country][state];
            if (Object.keys(currentState[country]).length === 0) {
                delete currentState[country];
            }
        }

        setLocationState(currentState);
        renderLocationRules();
    }

    function syncLocationStateFromInputs() {
        var currentState = {};

        document.querySelectorAll('.edw-location-rule').forEach(function(row) {
            var country = row.getAttribute('data-country');
            var state = row.getAttribute('data-state');
            var days = row.querySelector('.edw-rule-days');
            var maxDays = row.querySelector('.edw-rule-max-days');

            if (!country || !state) {
                return;
            }

            if (!currentState[country]) {
                currentState[country] = {};
            }

            currentState[country][state] = {
                days: Number(days ? days.value : 0),
                max_days: Number(maxDays ? maxDays.value : 0)
            };
        });

        setLocationState(currentState);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var config = getConfig();
        var countrySelect = document.querySelector('.edw-country-select');
        var addButton = document.querySelector('.edw-add-location-rule');
        var saveLocationButton = document.querySelector('.edw-save-location-rules');
        var form = getForm();
        var modeMessage = document.querySelector("select[name='_edw_mode']");
        var list = document.querySelector('.edw-location-rules-list');

        if (getHiddenInput() && !getHiddenInput().value) {
            getHiddenInput().value = JSON.stringify(config.locationRules || {});
        }

        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                populateStates(this.value);
            });
        }

        if (addButton) {
            addButton.addEventListener('click', addLocationRule);
        }

        if (saveLocationButton) {
            saveLocationButton.addEventListener('click', function() {
                syncLocationStateFromInputs();
                if (form) {
                    form.submit();
                }
            });
        }

        if (list) {
            list.addEventListener('click', function(event) {
                if (event.target.classList.contains('edw-remove-location-rule')) {
                    removeLocationRule(event.target);
                }
            });

            list.addEventListener('input', function(event) {
                if (event.target.classList.contains('edw-rule-days') || event.target.classList.contains('edw-rule-max-days')) {
                    syncLocationStateFromInputs();
                }
            });
        }

        if (modeMessage) {
            modeMessage.addEventListener('change', syncCustomMessageVisibility);
        }

        if (form) {
            form.addEventListener('submit', syncLocationStateFromInputs);
        }

        syncCustomMessageVisibility();
        renderLocationRules();
    });
})();
