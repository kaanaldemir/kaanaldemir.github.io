/* eslint-disable no-var */
'use strict';

(function () {
    const form = document.getElementById('editorForm');
    const statusMessage = document.getElementById('statusMessage');
    const resetButton = document.getElementById('resetButton');
    const restoreDefaultsButton = document.getElementById('restoreDefaultsButton');
    const savePresetButton = document.getElementById('savePresetButton');
    const presetsToggle = document.getElementById('presetsToggle');
    const presetMenu = document.getElementById('presetMenu');
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const upButton = document.getElementById('upButton');
    const downButton = document.getElementById('downButton');

    const heroContactsContainer = document.getElementById('heroContacts');
    const experienceJobsContainer = document.getElementById('experienceJobs');
    const projectsContainer = document.getElementById('projectsList');
    const skillsContainer = document.getElementById('skillsList');
    const educationContainer = document.getElementById('educationList');
    const contactMethodsContainer = document.getElementById('contactMethods');
    const pageSpeedContainer = document.getElementById('pageSpeedList');

    const defaultContent = deepClone(window.defaultContent || {});
    let originalContent = deepClone(window.initialContent || {});
    let presets = Object.assign({}, window.initialPresets || {});

    const sortableContainers = [
        heroContactsContainer,
        experienceJobsContainer,
        projectsContainer,
        skillsContainer,
        educationContainer,
        contactMethodsContainer,
        pageSpeedContainer
    ];

    sortableContainers.forEach(function (container) {
        enableSorting(container);
    });

    const sections = Array.from(document.querySelectorAll('main .editor-group'));

    function deepClone(obj) {
        return JSON.parse(JSON.stringify(obj || {}));
    }

    function setValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
        }
    }

    function showStatus(message, type) {
        statusMessage.textContent = message;
        statusMessage.classList.remove('success', 'error');
        if (type === 'success') {
            statusMessage.classList.add('success');
        } else if (type === 'error') {
            statusMessage.classList.add('error');
        }
    }

    function appendInput(entry, labelText, cssClass, value, placeholder) {
        const wrapper = document.createElement('div');
        wrapper.className = 'list-field';
        const label = document.createElement('label');
        label.textContent = labelText;
        const input = document.createElement('input');
        input.type = 'text';
        input.className = cssClass;
        input.value = value || '';
        if (placeholder) {
            input.placeholder = placeholder;
        }
        wrapper.appendChild(label);
        wrapper.appendChild(input);
        entry.appendChild(wrapper);
        return input;
    }

    function appendUrlInput(entry, labelText, cssClass, value, placeholder) {
        const wrapper = document.createElement('div');
        wrapper.className = 'list-field';
        const label = document.createElement('label');
        label.textContent = labelText;
        const input = document.createElement('input');
        input.type = 'url';
        input.className = cssClass;
        input.value = value || '';
        if (placeholder) {
            input.placeholder = placeholder;
        }
        wrapper.appendChild(label);
        wrapper.appendChild(input);
        entry.appendChild(wrapper);
        return input;
    }

    function appendTextarea(entry, labelText, cssClass, value, placeholder) {
        const wrapper = document.createElement('div');
        wrapper.className = 'list-field';
        const label = document.createElement('label');
        label.textContent = labelText;
        const textarea = document.createElement('textarea');
        textarea.className = cssClass;
        textarea.value = value || '';
        if (placeholder) {
            textarea.placeholder = placeholder;
        }
        wrapper.appendChild(label);
        wrapper.appendChild(textarea);
        entry.appendChild(wrapper);
        return textarea;
    }

    function createListEntry() {
        const entry = document.createElement('div');
        entry.className = 'list-entry';
        entry.setAttribute('draggable', 'true');

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-entry';
        removeBtn.innerHTML = '<i class="fa fa-times"></i>';
        removeBtn.addEventListener('click', function () {
            entry.remove();
        });
        entry.appendChild(removeBtn);

        const handle = document.createElement('button');
        handle.type = 'button';
        handle.className = 'drag-handle';
        handle.innerHTML = '<i class="fa fa-grip-lines"></i>';
        entry.appendChild(handle);

        return entry;
    }
    function createHeroContactRow(contact) {
        const entry = createListEntry();
        appendInput(entry, 'Icon class', 'hero-icon', contact && contact.icon, 'fa fa-envelope');
        appendInput(entry, 'Title', 'hero-title', contact && contact.title, 'Email');
        appendInput(entry, 'URL', 'hero-url', contact && contact.url, 'mailto:you@example.com');
        heroContactsContainer.appendChild(entry);
        return entry;
    }

    function createJobRow(job) {
        const entry = createListEntry();
        appendInput(entry, 'Position', 'job-position', job && job.position, 'Company - Role');
        appendInput(entry, 'Location & dates', 'job-location', job && job.location, 'City - Dates');
        appendTextarea(entry, 'Bullet points (one per line)', 'job-bullets', job && job.bullets ? job.bullets.join('\n') : '', 'Accomplishment...');
        experienceJobsContainer.appendChild(entry);
        return entry;
    }

    function createProjectRow(project) {
        const entry = createListEntry();
        appendInput(entry, 'Name', 'project-name', project && project.name, 'Project name');
        appendInput(entry, 'Description', 'project-description', project && project.description, 'Short description');
        projectsContainer.appendChild(entry);
        return entry;
    }

    function createSkillRow(skill) {
        const entry = createListEntry();
        appendInput(entry, 'Skill name', 'skill-name', skill && skill.name, 'Skill');
        appendInput(entry, 'Detail (optional)', 'skill-description', skill && skill.description, 'More info');
        skillsContainer.appendChild(entry);
        return entry;
    }

    function createEducationRow(item) {
        const entry = createListEntry();
        appendInput(entry, 'Entry', 'education-text', item && item.text, 'Institution - Detail');
        educationContainer.appendChild(entry);
        return entry;
    }

    function createContactMethodRow(method) {
        const entry = createListEntry();
        appendInput(entry, 'Icon class', 'contact-icon', method && method.icon, 'fa fa-envelope');
        appendInput(entry, 'Label', 'contact-label', method && method.label, 'Email');
        appendInput(entry, 'Display value', 'contact-value', method && method.value, 'you@example.com');
        appendInput(entry, 'URL', 'contact-url', method && method.url, 'mailto:you@example.com');
        contactMethodsContainer.appendChild(entry);
        return entry;
    }

    function createPageSpeedRow(metric) {
        const entry = createListEntry();
        appendInput(entry, 'Label', 'pagespeed-label', metric && metric.label, 'Performance');
        appendInput(entry, 'Score', 'pagespeed-score', metric && metric.score, '100%');
        appendUrlInput(entry, 'URL', 'pagespeed-url', metric && metric.url, 'https://...');
        pageSpeedContainer.appendChild(entry);
        return entry;
    }

    function populateForm(content) {
        const meta = content.meta || {};
        setValue('metaTitle', meta.title || '');
        setValue('metaAuthor', meta.author || '');
        setValue('metaDescription', meta.description || '');
        setValue('metaCanonical', meta.canonical || '');
        setValue('metaOgImage', meta.og_image || '');

        const hero = content.hero || {};
        setValue('heroHeadline', hero.headline || '');
        setValue('heroName', hero.name || '');
        setValue('heroJobTitle', hero.job_title || '');
        setValue('heroTypewriter', hero.typewriter || '');
        setValue('heroProfileImage', hero.profile_image || '');
        setValue('heroEmail', hero.email || '');
        setValue('heroPhone', hero.phone || '');
        const heroLocation = hero.location || {};
        setValue('heroLocationLocality', heroLocation.locality || '');
        setValue('heroLocationRegion', heroLocation.region || '');
        setValue('heroLocationCountry', heroLocation.country || '');

        heroContactsContainer.innerHTML = '';
        (hero.contacts || []).forEach(function (item) {
            createHeroContactRow(item);
        });

        const objective = content.objective || {};
        setValue('objectiveTitle', objective.title || '');
        setValue('objectiveNavLabel', objective.nav_label || '');
        setValue('objectiveBody', objective.body || '');

        const experience = content.experience || {};
        setValue('experienceTitle', experience.title || '');
        setValue('experienceNavLabel', experience.nav_label || '');
        experienceJobsContainer.innerHTML = '';
        (experience.jobs || []).forEach(function (item) {
            createJobRow(item);
        });

        const projects = content.projects || {};
        setValue('projectsTitle', projects.title || '');
        setValue('projectsNavLabel', projects.nav_label || '');
        projectsContainer.innerHTML = '';
        (projects.items || []).forEach(function (item) {
            createProjectRow(item);
        });

        const skills = content.skills || {};
        setValue('skillsTitle', skills.title || '');
        setValue('skillsNavLabel', skills.nav_label || '');
        skillsContainer.innerHTML = '';
        (skills.items || []).forEach(function (item) {
            createSkillRow(item);
        });

        const education = content.education || {};
        setValue('educationTitle', education.title || '');
        setValue('educationNavLabel', education.nav_label || '');
        educationContainer.innerHTML = '';
        (education.entries || []).forEach(function (item) {
            createEducationRow(item);
        });

        const contact = content.contact || {};
        setValue('contactTitle', contact.title || '');
        setValue('contactNavLabel', contact.nav_label || '');
        contactMethodsContainer.innerHTML = '';
        (contact.methods || []).forEach(function (item) {
            createContactMethodRow(item);
        });

        const about = content.about || {};
        setValue('aboutTitle', about.title || '');
        setValue('aboutBody', about.body || '');
        setValue('aboutNote', about.note || '');
        pageSpeedContainer.innerHTML = '';
        (about.page_speed || []).forEach(function (item) {
            createPageSpeedRow(item);
        });

        const footer = content.footer || {};
        setValue('footerText', footer.text || '');

        attachDefaultButtons();
    }
    function getDefaultValue(path) {
        if (!path) {
            return undefined;
        }
        const segments = path.split('.');
        let value = defaultContent;
        for (let i = 0; i < segments.length; i += 1) {
            if (value === undefined || value === null) {
                return undefined;
            }
            value = value[segments[i]];
        }
        if (Array.isArray(value)) {
            return value.join('\n');
        }
        if (value !== null && typeof value === 'object') {
            return undefined;
        }
        if (value === undefined || value === null) {
            return undefined;
        }
        return String(value);
    }

    function attachDefaultButtons() {
        const controls = document.querySelectorAll('.field-control');
        controls.forEach(function (control) {
            const field = control.querySelector('input[data-content-path], textarea[data-content-path]');
            if (!field) {
                return;
            }
            if (field.closest('.list-entry')) {
                const existing = control.querySelector('.field-reset');
                if (existing) {
                    existing.remove();
                }
                return;
            }
            const path = field.dataset.contentPath;
            if (!path) {
                return;
            }
            const defaultValue = getDefaultValue(path);
            let button = control.querySelector('.field-reset');
            if (typeof defaultValue === 'undefined') {
                if (button) {
                    button.remove();
                }
                return;
            }
            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'field-reset';
                button.textContent = 'Default';
                button.addEventListener('click', function () {
                    field.value = defaultValue;
                    triggerInput(field);
                    showStatus('Default value restored for this field. Save to publish changes.', 'success');
                });
                control.appendChild(button);
            }
        });
    }

    function triggerInput(el) {
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function enableSorting(container) {
        if (!container) {
            return;
        }
        let dragged = null;
        container.addEventListener('dragstart', function (event) {
            const entry = event.target.closest('.list-entry');
            if (!entry) {
                event.preventDefault();
                return;
            }
            dragged = entry;
            entry.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', '');
        });

        container.addEventListener('dragover', function (event) {
            if (!dragged) {
                return;
            }
            event.preventDefault();
            const entry = event.target.closest('.list-entry');
            if (!entry || entry === dragged) {
                return;
            }
            const rect = entry.getBoundingClientRect();
            const after = (event.clientY - rect.top) > rect.height / 2;
            if (after) {
                entry.after(dragged);
            } else {
                entry.before(dragged);
            }
        });

        container.addEventListener('dragend', function (event) {
            const entry = event.target.closest('.list-entry');
            if (entry) {
                entry.classList.remove('dragging');
            }
            dragged = null;
        });
    }

    function collectListEntries(container, selectorMap, transform) {
        const rows = Array.from(container.querySelectorAll('.list-entry'));
        return rows.map(function (row) {
            const values = {};
            Object.keys(selectorMap).forEach(function (key) {
                const selector = selectorMap[key];
                const el = row.querySelector(selector);
                values[key] = el ? el.value.trim() : '';
            });
            return transform ? transform(values) : values;
        }).filter(function (item) {
            return Object.values(item).some(function (val) {
                if (Array.isArray(val)) {
                    return val.length > 0;
                }
                return typeof val === 'string' ? val !== '' : val != null;
            });
        });
    }

    function buildPayload() {
        return {
            meta: {
                title: getTrimmedValue('metaTitle'),
                author: getTrimmedValue('metaAuthor'),
                description: getTrimmedValue('metaDescription'),
                canonical: getTrimmedValue('metaCanonical'),
                og_image: getTrimmedValue('metaOgImage')
            },
            hero: {
                headline: getTrimmedValue('heroHeadline'),
                name: getTrimmedValue('heroName'),
                job_title: getTrimmedValue('heroJobTitle'),
                typewriter: getTrimmedValue('heroTypewriter'),
                profile_image: getTrimmedValue('heroProfileImage'),
                email: getTrimmedValue('heroEmail'),
                phone: getTrimmedValue('heroPhone'),
                location: {
                    locality: getTrimmedValue('heroLocationLocality'),
                    region: getTrimmedValue('heroLocationRegion'),
                    country: getTrimmedValue('heroLocationCountry')
                },
                contacts: collectListEntries(heroContactsContainer, {
                    icon: '.hero-icon',
                    title: '.hero-title',
                    url: '.hero-url'
                })
            },
            objective: {
                title: getTrimmedValue('objectiveTitle'),
                nav_label: getTrimmedValue('objectiveNavLabel'),
                body: getTrimmedValue('objectiveBody')
            },
            experience: {
                title: getTrimmedValue('experienceTitle'),
                nav_label: getTrimmedValue('experienceNavLabel'),
                jobs: collectListEntries(experienceJobsContainer, {
                    position: '.job-position',
                    location: '.job-location',
                    bullets: '.job-bullets'
                }, function (values) {
                    const bullets = values.bullets
                        ? values.bullets.split('\n').map(function (line) {
                            return line.trim();
                        }).filter(Boolean)
                        : [];
                    return {
                        position: values.position,
                        location: values.location,
                        bullets: bullets
                    };
                })
            },
            projects: {
                title: getTrimmedValue('projectsTitle'),
                nav_label: getTrimmedValue('projectsNavLabel'),
                items: collectListEntries(projectsContainer, {
                    name: '.project-name',
                    description: '.project-description'
                })
            },
            skills: {
                title: getTrimmedValue('skillsTitle'),
                nav_label: getTrimmedValue('skillsNavLabel'),
                items: collectListEntries(skillsContainer, {
                    name: '.skill-name',
                    description: '.skill-description'
                })
            },
            education: {
                title: getTrimmedValue('educationTitle'),
                nav_label: getTrimmedValue('educationNavLabel'),
                entries: collectListEntries(educationContainer, {
                    text: '.education-text'
                })
            },
            contact: {
                title: getTrimmedValue('contactTitle'),
                nav_label: getTrimmedValue('contactNavLabel'),
                methods: collectListEntries(contactMethodsContainer, {
                    icon: '.contact-icon',
                    label: '.contact-label',
                    value: '.contact-value',
                    url: '.contact-url'
                })
            },
            about: {
                title: getTrimmedValue('aboutTitle'),
                body: getTrimmedValue('aboutBody'),
                note: getTrimmedValue('aboutNote'),
                page_speed: collectListEntries(pageSpeedContainer, {
                    label: '.pagespeed-label',
                    score: '.pagespeed-score',
                    url: '.pagespeed-url'
                })
            },
            footer: {
                text: getTrimmedValue('footerText')
            }
        };
    }

    function getTrimmedValue(id) {
        const el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }
    async function handleSubmit(event) {
        event.preventDefault();
        const payload = buildPayload();
        try {
            const response = await fetch('save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Save failed');
            }
            showStatus('Content saved successfully.', 'success');
            originalContent = deepClone(payload);
        } catch (error) {
            showStatus(error.message || 'Unable to save content.', 'error');
        }
    }

    function handleReset() {
        populateForm(deepClone(originalContent));
        showStatus('Changes reverted.', 'success');
    }

    function sendPresetRequest(action, payload) {
        return fetch('presets.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.assign({ action: action }, payload))
        }).then(function (response) {
            return response.text().then(function (text) {
                let data = null;
                if (text) {
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        throw new Error('Preset response was not valid JSON.');
                    }
                } else {
                    data = {};
                }
                if (!response.ok || data.error) {
                    const message = data && data.error ? data.error : 'Preset request failed.';
                    throw new Error(message);
                }
                return data;
            });
        });
    }

    function renderPresetMenu() {
        presetMenu.innerHTML = '';
        const names = Object.keys(presets).sort(function (a, b) { return a.localeCompare(b); });
        if (!names.length) {
            const empty = document.createElement('div');
            empty.className = 'preset-empty';
            empty.textContent = 'No presets saved yet.';
            presetMenu.appendChild(empty);
            return;
        }
        names.forEach(function (name) {
            const item = document.createElement('div');
            item.className = 'preset-item';
            const loadBtn = document.createElement('button');
            loadBtn.type = 'button';
            loadBtn.className = 'preset-load-btn';
            loadBtn.textContent = name;
            loadBtn.addEventListener('click', function () {
                if (!confirm('Load preset "' + name + '"? Unsaved changes will be lost.')) {
                    return;
                }
                sendPresetRequest('load', { name: name }).then(function (data) {
                    populateForm(data.content || {});
                    showStatus('Preset "' + name + '" loaded.', 'success');
                    presetMenu.classList.remove('open');
                    presetsToggle.setAttribute('aria-expanded', 'false');
                }).catch(function (error) {
                    showStatus(error.message, 'error');
                });
            });
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'preset-delete-btn';
            deleteBtn.setAttribute('aria-label', 'Delete preset ' + name);
            deleteBtn.title = 'Delete preset';
            deleteBtn.innerHTML = '<i class="fa fa-trash"></i><span>X</span>';
            deleteBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                if (!confirm('Delete preset "' + name + '"?')) {
                    return;
                }
                sendPresetRequest('delete', { name: name }).then(function (data) {
                    presets = data.presets || {};
                    renderPresetMenu();
                    showStatus('Preset deleted.', 'success');
                }).catch(function (error) {
                    showStatus(error.message, 'error');
                });
            });
            item.appendChild(loadBtn);
            item.appendChild(deleteBtn);
            presetMenu.appendChild(item);
        });
    }

    if (savePresetButton) {
        savePresetButton.addEventListener('click', function () {
            const name = prompt('Preset name');
            if (name === null) {
                return;
            }
            const trimmedName = name.trim();
            if (!trimmedName) {
                showStatus('Preset name cannot be empty.', 'error');
                return;
            }
            sendPresetRequest('save', { name: trimmedName, content: buildPayload() }).then(function (data) {
                presets = data.presets || {};
                renderPresetMenu();
                showStatus('Preset saved.', 'success');
            }).catch(function (error) {
                showStatus(error.message, 'error');
            });
        });
    }

    if (presetsToggle) {
        presetsToggle.addEventListener('click', function () {
            const isOpen = presetMenu.classList.toggle('open');
            presetsToggle.setAttribute('aria-expanded', String(isOpen));
        });
    }

    document.addEventListener('click', function (event) {
        if (!presetMenu.contains(event.target) && event.target !== presetsToggle) {
            presetMenu.classList.remove('open');
            presetsToggle.setAttribute('aria-expanded', 'false');
        }
        const button = event.target.closest('button[data-action]');
        if (!button) {
            return;
        }
        const action = button.dataset.action;
        let handled = true;
        switch (action) {
            case 'add-hero-contact':
                createHeroContactRow({});
                break;
            case 'add-job':
                createJobRow({});
                break;
            case 'add-project':
                createProjectRow({});
                break;
            case 'add-skill':
                createSkillRow({});
                break;
            case 'add-education':
                createEducationRow({});
                break;
            case 'add-contact-method':
                createContactMethodRow({});
                break;
            case 'add-pagespeed':
                createPageSpeedRow({});
                break;
            default:
                handled = false;
                break;
        }
        if (handled) {
            attachDefaultButtons();
        }
    });

    form.addEventListener('submit', handleSubmit);
    resetButton.addEventListener('click', handleReset);

    if (restoreDefaultsButton) {
        restoreDefaultsButton.addEventListener('click', function () {
            populateForm(deepClone(defaultContent));
            showStatus('Default content loaded. Save to publish changes.', 'success');
        });
    }

    const THEME_KEY = 'portfolio-editor-theme';

    function applyTheme(mode) {
        const isLight = mode === 'light';
        document.body.classList.toggle('dark-mode', !isLight);
        if (themeToggleBtn) {
            const icon = themeToggleBtn.querySelector('i');
            const label = themeToggleBtn.querySelector('span');
            if (icon && label) {
                icon.className = isLight ? 'fa fa-moon' : 'fa fa-sun';
                label.textContent = isLight ? 'Dark' : 'Light';
            }
        }
        localStorage.setItem(THEME_KEY, isLight ? 'light' : 'dark');
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const current = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
            applyTheme(current === 'light' ? 'dark' : 'light');
        });
    }

    const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
    applyTheme(savedTheme);

    function getCurrentSectionIndex() {
        const scrollPos = window.scrollY;
        let active = 0;
        sections.forEach(function (section, index) {
            if (scrollPos >= section.offsetTop - 80) {
                active = index;
            }
        });
        return active;
    }

    function scrollToSection(index) {
        if (index < 0) {
            index = 0;
        }
        if (index >= sections.length) {
            index = sections.length - 1;
        }
        sections[index].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    if (downButton) {
        downButton.addEventListener('click', function () {
            const current = getCurrentSectionIndex();
            scrollToSection(Math.min(sections.length - 1, current + 1));
        });
    }

    if (upButton) {
        upButton.addEventListener('click', function () {
            const current = getCurrentSectionIndex();
            scrollToSection(Math.max(0, current - 1));
        });
    }

    window.addEventListener('scroll', function () {
        document.body.style.setProperty('--bg-y', `${-window.pageYOffset * 0.2}px`);
    });

    populateForm(deepClone(originalContent));
    renderPresetMenu();
})();
