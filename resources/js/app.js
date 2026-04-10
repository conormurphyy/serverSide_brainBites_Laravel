import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
	initializeMobileNav();
	initializeThemeToggle();
	initializeTopicMap();
	initializePageHero3D();
	initializeTiltCards();
	initializePostPreview();
	initializeReadingTools();
	initializeDeletePrompts();
	initializeBrainBot();
	initializeBackToTop();
	initializeKeyboardShortcuts();
	initializeCopyLinks();
	initializeRecentViews();
	initializeDraftAutosave();
	initializeActionFeedback();

	const counterInputs = document.querySelectorAll('[data-counter-target]');

	counterInputs.forEach((input) => {
		const targetId = input.getAttribute('data-counter-target');
		const target = targetId ? document.getElementById(targetId) : null;

		if (!target) {
			return;
		}

		const updateCount = () => {
			target.textContent = String(input.value.length);
		};

		input.addEventListener('input', updateCount);
		updateCount();
	});

	const imageInput = document.getElementById('image');
	const imageLabel = document.getElementById('imageName');

	if (imageInput && imageLabel) {
		imageInput.addEventListener('change', () => {
			imageLabel.textContent = imageInput.files?.[0]?.name ?? 'No file selected';
		});
	}
});

async function initializeTopicMap() {
	const canvas = document.getElementById('topic-map-canvas');
	const dataEl = document.getElementById('topic-map-data');

	if (!canvas || !dataEl || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	let mapData;

	try {
		mapData = JSON.parse(dataEl.textContent || '[]');
	} catch {
		mapData = [];
	}

	if (!Array.isArray(mapData) || !mapData.length) {
		return;
	}

	const THREE = await import('three');
	const scene = new THREE.Scene();
	const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
	camera.position.set(0, 0.2, 7.5);

	const renderer = new THREE.WebGLRenderer({
		canvas,
		alpha: true,
		antialias: true,
	});
	renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

	const mapGroup = new THREE.Group();
	scene.add(mapGroup);

	scene.add(new THREE.AmbientLight(0xc2fcff, 0.75));

	const keyLight = new THREE.DirectionalLight(0xffffff, 1.25);
	keyLight.position.set(3, 4, 8);
	scene.add(keyLight);

	const rimLight = new THREE.PointLight(0xff9764, 1.1, 28);
	rimLight.position.set(-4, -2, 4);
	scene.add(rimLight);

	const core = new THREE.Mesh(
		new THREE.IcosahedronGeometry(0.9, 1),
		new THREE.MeshStandardMaterial({
			color: 0x4ef8ff,
			emissive: 0x083f5f,
			metalness: 0.4,
			roughness: 0.2,
			flatShading: true,
		})
	);
	mapGroup.add(core);

	const shell = new THREE.Mesh(
		new THREE.TorusGeometry(1.55, 0.04, 16, 120),
		new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.38 })
	);
	shell.rotation.x = 1.2;
	shell.rotation.y = 0.4;
	mapGroup.add(shell);

	const particleCloud = createParticleCloud(THREE);
	mapGroup.add(particleCloud);

	const palette = [0x53e8ff, 0x9dff6a, 0xffa163, 0xffd35c, 0x8fc8ff, 0xc2ff7f, 0xfab0ff];
	const nodes = mapData.map((item, index) => {
		const radius = 0.24 + Math.min(0.45, item.count * 0.04);
		const material = new THREE.MeshStandardMaterial({
			color: palette[index % palette.length],
			emissive: 0x10233f,
			metalness: 0.35,
			roughness: 0.25,
		});
		const sphere = new THREE.Mesh(new THREE.SphereGeometry(radius, 20, 20), material);

		sphere.userData = {
			slug: item.slug,
			name: item.name,
			count: item.count,
			latestTitle: item.latestTitle,
		};

		mapGroup.add(sphere);

		const orbitRadius = 2 + (index * 0.43);
		const speed = 0.23 + ((index % 4) * 0.05);
		const angleOffset = (index / Math.max(mapData.length, 1)) * Math.PI * 2;

		return {
			sphere,
			orbitRadius,
			speed,
			angleOffset,
		};
	});

	const mapWrapper = canvas.closest('[data-topic-map-wrapper]');
	const titleEl = document.getElementById('topicMapTitle');
	const metaEl = document.getElementById('topicMapMeta');
	const hintEl = document.getElementById('topicMapHint');
	const legendItems = [...document.querySelectorAll('[data-map-category-slug]')];
	const raycaster = new THREE.Raycaster();
	const pointer = new THREE.Vector2();
	let activeSlug = null;

	const setActive = (slug) => {
		activeSlug = slug;
		legendItems.forEach((item) => {
			item.classList.toggle('is-active', item.dataset.mapCategorySlug === slug);
		});

		if (!slug) {
			if (titleEl) titleEl.textContent = 'Hover a topic node';
			if (metaEl) metaEl.textContent = 'See post volume and latest question.';
			if (hintEl) hintEl.textContent = 'Tip: click a node to filter';
			return;
		}

		const active = mapData.find((item) => item.slug === slug);
		if (!active) return;

		if (titleEl) titleEl.textContent = active.name;
		if (metaEl) metaEl.textContent = `${active.count} public posts`;
		if (hintEl) hintEl.textContent = active.latestTitle ? `Latest: ${active.latestTitle}` : 'No latest post yet';
	};

	if (mapWrapper) {
		mapWrapper.addEventListener('pointermove', (event) => {
			const rect = canvas.getBoundingClientRect();
			pointer.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
			pointer.y = -(((event.clientY - rect.top) / rect.height) * 2 - 1);
		});

		mapWrapper.addEventListener('pointerleave', () => {
			setActive(null);
			pointer.set(10, 10);
		});

		mapWrapper.addEventListener('click', () => {
			if (!activeSlug) return;
			const targetLink = legendItems.find((item) => item.dataset.mapCategorySlug === activeSlug);
			if (targetLink) {
				window.location.href = targetLink.href;
			}
		});
	}

	const resize = () => {
		const width = canvas.clientWidth;
		const height = canvas.clientHeight;
		if (!width || !height) return;

		renderer.setSize(width, height, false);
		camera.aspect = width / height;
		camera.updateProjectionMatrix();
	};

	resize();
	window.addEventListener('resize', resize);

	const clock = new THREE.Clock();

	const animate = () => {
		const elapsed = clock.getElapsedTime();
		core.rotation.x = elapsed * 0.33;
		core.rotation.y = elapsed * 0.45;
		shell.rotation.z = elapsed * 0.18;
		particleCloud.rotation.y = elapsed * 0.03;

		nodes.forEach((node, index) => {
			const angle = elapsed * node.speed + node.angleOffset;
			node.sphere.position.x = Math.cos(angle) * node.orbitRadius;
			node.sphere.position.z = Math.sin(angle) * node.orbitRadius;
			node.sphere.position.y = Math.sin(elapsed * (node.speed + 0.18) + index) * 0.55;
			node.sphere.rotation.y += 0.015;
		});

		raycaster.setFromCamera(pointer, camera);
		const hits = raycaster.intersectObjects(nodes.map((node) => node.sphere));

		if (hits.length > 0) {
			const slug = hits[0].object.userData.slug;
			if (slug !== activeSlug) {
				setActive(slug);
			}
		} else if (activeSlug !== null) {
			setActive(null);
		}

		renderer.render(scene, camera);
		window.requestAnimationFrame(animate);
	};

	animate();
}

function initializeBrainBot() {
	const panel = document.getElementById('brainbotPanel');
	const toggle = document.getElementById('brainbotToggle');
	const form = document.getElementById('brainbotForm');
	const input = document.getElementById('brainbotInput');
	const messages = document.getElementById('brainbotMessages');
	const quickPrompts = document.querySelectorAll('[data-brainbot-prompt]');
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	if (!panel || !form || !input || !messages || !csrfToken) {
		return;
	}

	const addMessage = (text, role) => {
		const bubble = document.createElement('article');
		bubble.className = `bb-brainbot-message ${role}`;
		bubble.textContent = text;
		messages.appendChild(bubble);
		messages.scrollTop = messages.scrollHeight;
	};

	const addHistoryPair = (question, answer) => {
		if (question) {
			addMessage(question, 'user');
		}
		if (answer) {
			addMessage(answer, 'bot');
		}
	};

	if (toggle) {
		const setOpen = (open) => {
			panel.hidden = !open;
			toggle.setAttribute('aria-expanded', String(open));
			if (open) {
				input.focus();
			}
		};

		toggle.addEventListener('click', () => {
			setOpen(panel.hidden);
		});
	}

	quickPrompts.forEach((button) => {
		button.addEventListener('click', () => {
			const prompt = button.getAttribute('data-brainbot-prompt') || '';
			if (!prompt) {
				return;
			}

			input.value = prompt;
			input.focus();
		});
	});

	fetch('/brainbot/history', {
		method: 'GET',
		headers: {
			'Accept': 'application/json',
		},
	})
		.then(async (response) => {
			if (!response.ok) {
				return;
			}

			const payload = await response.json();
			const history = Array.isArray(payload.history) ? payload.history : [];

			if (!history.length) {
				return;
			}

			messages.innerHTML = '';
			history.forEach((item) => {
				addHistoryPair(item.question || '', item.answer || '');
			});
		})
		.catch(() => {
			// Ignore history loading failures silently.
		});

	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		const message = input.value.trim();

		if (!message) {
			return;
		}

		addMessage(message, 'user');
		input.value = '';
		input.disabled = true;

		const loading = document.createElement('article');
		loading.className = 'bb-brainbot-message bot is-loading';
		loading.textContent = 'brainBot is thinking...';
		messages.appendChild(loading);
		messages.scrollTop = messages.scrollHeight;

		try {
			const response = await fetch('/brainbot/chat', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json',
				},
				body: JSON.stringify({ message }),
			});

			const data = await response.json();
			loading.remove();

			if (!response.ok) {
				addMessage('I hit an error while answering. Please try again.', 'bot');
				return;
			}

			addMessage(data.answer || 'I could not generate an answer yet.', 'bot');

			if (Array.isArray(data.sources) && data.sources.length) {
				const sourceList = data.sources.slice(0, 3)
					.map((source, index) => `${index + 1}. ${source.title} (${source.url})`)
					.join('\n');
				addMessage(`Sources:\n${sourceList}`, 'bot');
			}
		} catch {
			loading.remove();
			addMessage('I could not reach the service right now. Please try again.', 'bot');
		} finally {
			input.disabled = false;
			input.focus();
		}
	});
}

function initializeThemeToggle() {
	const root = document.body;
	const toggles = [...document.querySelectorAll('[data-theme-toggle]')];
	const key = 'bb-theme';

	const applyTheme = (theme) => {
		const dark = theme === 'dark';
		root.classList.toggle('theme-dark', dark);
		toggles.forEach((toggle) => {
			toggle.textContent = dark ? 'Light mode' : 'Dark mode';
		});
	};

	const saved = localStorage.getItem(key);
	if (saved === 'dark' || saved === 'light') {
		applyTheme(saved);
	} else {
		const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
		applyTheme(prefersDark ? 'dark' : 'light');
	}

	if (!toggles.length) {
		return;
	}

	toggles.forEach((toggle) => {
		toggle.addEventListener('click', () => {
			const next = root.classList.contains('theme-dark') ? 'light' : 'dark';
			localStorage.setItem(key, next);
			applyTheme(next);
			showToast(`Theme switched to ${next}.`);
		});
	});
}

function showToast(message) {
	const toast = document.getElementById('bbToast');
	if (!toast) return;

	toast.textContent = message;
	toast.hidden = false;

	window.clearTimeout(showToast.timer);
	showToast.timer = window.setTimeout(() => {
		toast.hidden = true;
	}, 1800);
}

showToast.timer = 0;

function initializeBackToTop() {
	const button = document.getElementById('backToTop');
	if (!button) return;

	const sync = () => {
		button.hidden = window.scrollY < 360;
	};

	window.addEventListener('scroll', sync, { passive: true });
	button.addEventListener('click', () => {
		window.scrollTo({ top: 0, behavior: 'smooth' });
	});

	sync();
}

function initializeKeyboardShortcuts() {
	let gPressedAt = 0;

	document.addEventListener('keydown', (event) => {
		const target = event.target;
		const typing = target instanceof HTMLElement
			&& (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable);

		if (typing && event.key !== 'Escape') {
			return;
		}

		if (event.key === '/') {
			event.preventDefault();
			const search = document.getElementById('search');
			if (search instanceof HTMLInputElement) {
				search.focus();
				search.select();
			}
			return;
		}

		if (event.key.toLowerCase() === 'n') {
			const createLink = document.querySelector('a[href*="/posts/create"]');
			if (createLink instanceof HTMLAnchorElement) {
				window.location.href = createLink.href;
			}
			return;
		}

		if (event.key.toLowerCase() === 'g') {
			gPressedAt = Date.now();
			return;
		}

		if (event.key.toLowerCase() === 'h' && Date.now() - gPressedAt < 800) {
			window.location.href = '/';
		}
	});
}

function initializeCopyLinks() {
	const buttons = document.querySelectorAll('[data-copy-url]');
	if (!buttons.length) return;

	buttons.forEach((button) => {
		button.addEventListener('click', async () => {
			const url = button.getAttribute('data-copy-url');
			if (!url) return;

			try {
				await navigator.clipboard.writeText(url);
				showToast('Link copied.');
			} catch {
				showToast('Could not copy link.');
			}
		});
	});
}

function initializeRecentViews() {
	const marker = document.querySelector('[data-recent-view-post]');
	const list = document.getElementById('recentViews');
	const key = 'bb-recent-posts';

	if (marker) {
		try {
			const current = {
				title: marker.getAttribute('data-title') || '',
				url: marker.getAttribute('data-url') || '',
				category: marker.getAttribute('data-category') || '',
			};

			const saved = JSON.parse(localStorage.getItem(key) || '[]');
			const cleaned = Array.isArray(saved) ? saved.filter((item) => item && item.url && item.url !== current.url) : [];
			const next = [current, ...cleaned].slice(0, 3);
			localStorage.setItem(key, JSON.stringify(next));
		} catch {
			// Ignore localStorage errors.
		}
	}

	if (list) {
		try {
			const saved = JSON.parse(localStorage.getItem(key) || '[]');
			const entries = Array.isArray(saved) ? saved.slice(0, 3) : [];

			if (!entries.length) {
				list.innerHTML = '<p class="text-sm text-slate-600">Your recently viewed posts will appear here.</p>';
				return;
			}

			list.innerHTML = entries.map((item) => {
				const safeTitle = String(item.title || 'Untitled').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				const safeCategory = String(item.category || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				const safeUrl = String(item.url || '#').replace(/"/g, '&quot;');

				return `<a class="bb-card block" href="${safeUrl}"><p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">${safeCategory || 'Post'}</p><p class="mt-1 text-sm font-semibold text-slate-900">${safeTitle}</p></a>`;
			}).join('');
		} catch {
			list.innerHTML = '<p class="text-sm text-slate-600">Recent views are unavailable in this browser.</p>';
		}
	}
}

function initializeDraftAutosave() {
	const form = document.querySelector('[data-draft-form]');
	if (!(form instanceof HTMLFormElement)) return;

	const key = form.getAttribute('data-draft-key');
	if (!key) return;

	const fields = ['title', 'summary', 'body', 'category_id', 'published_at'];

	const save = () => {
		const payload = {};
		fields.forEach((id) => {
			const element = form.querySelector(`#${id}`);
			if (element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement || element instanceof HTMLSelectElement) {
				payload[id] = element.value;
			}
		});

		const checkbox = form.querySelector('input[name="is_public"]');
		if (checkbox instanceof HTMLInputElement) {
			payload.is_public = checkbox.checked;
		}

		localStorage.setItem(key, JSON.stringify(payload));
	};

	const load = () => {
		const raw = localStorage.getItem(key);
		if (!raw) return;

		let payload;
		try {
			payload = JSON.parse(raw);
		} catch {
			return;
		}

		fields.forEach((id) => {
			const element = form.querySelector(`#${id}`);
			if (!(element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement || element instanceof HTMLSelectElement)) return;
			if (String(element.value || '').trim() !== '') return;
			if (typeof payload[id] === 'string') {
				element.value = payload[id];
			}
		});

		const checkbox = form.querySelector('input[name="is_public"]');
		if (checkbox instanceof HTMLInputElement && typeof payload.is_public === 'boolean') {
			checkbox.checked = payload.is_public;
		}
	};

	load();

	form.addEventListener('input', save);
	form.addEventListener('change', save);
	form.addEventListener('submit', () => {
		localStorage.removeItem(key);
	});
}

function initializeActionFeedback() {
	document.querySelectorAll('form[action*="/like"], form[action*="/bookmark"]').forEach((form) => {
		if (!(form instanceof HTMLFormElement)) return;

		form.addEventListener('submit', (event) => {
			const submitter = event.submitter;
			if (submitter instanceof HTMLElement) {
				submitter.classList.add('bb-action-pulse');
			}
		});
	});
}

function initializeMobileNav() {
	const toggle = document.getElementById('mobileNavToggle');
	const panel = document.getElementById('mobileNavPanel');

	if (!toggle || !panel) {
		return;
	}

	if (toggle.dataset.navBound === 'true') {
		return;
	}

	toggle.dataset.navBound = 'true';

	const setOpen = (open) => {
		panel.classList.toggle('hidden', !open);
		toggle.setAttribute('aria-expanded', String(open));
		toggle.textContent = open ? 'Close' : 'Menu';
	};

	setOpen(!panel.classList.contains('hidden'));

	toggle.addEventListener('click', () => {
		const open = panel.classList.contains('hidden');
		setOpen(open);
	});

	panel.querySelectorAll('a, button[type="submit"]').forEach((element) => {
		element.addEventListener('click', () => {
			setOpen(false);
		});
	});

	window.addEventListener('resize', () => {
		if (window.innerWidth >= 768) {
			setOpen(false);
		}
	});
}

function createParticleCloud(THREE) {
	const particleCount = 900;
	const spread = 8;
	const positions = new Float32Array(particleCount * 3);

	for (let i = 0; i < particleCount; i += 1) {
		const radius = spread * Math.sqrt(Math.random());
		const theta = Math.random() * Math.PI * 2;
		const phi = Math.acos((Math.random() * 2) - 1);

		positions[i * 3] = radius * Math.sin(phi) * Math.cos(theta);
		positions[(i * 3) + 1] = radius * Math.sin(phi) * Math.sin(theta);
		positions[(i * 3) + 2] = radius * Math.cos(phi);
	}

	const geometry = new THREE.BufferGeometry();
	geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

	return new THREE.Points(
		geometry,
		new THREE.PointsMaterial({
			color: 0xffffff,
			size: 0.03,
			transparent: true,
			opacity: 0.7,
		})
	);
}

function initializePostPreview() {
	const titleInput = document.getElementById('title');
	const summaryInput = document.getElementById('summary');
	const categoryInput = document.getElementById('category_id');
	const imageInput = document.getElementById('image');

	const previewTitle = document.getElementById('previewTitle');
	const previewSummary = document.getElementById('previewSummary');
	const previewCategory = document.getElementById('previewCategory');
	const previewImage = document.getElementById('previewImage');

	if (!titleInput || !summaryInput || !previewTitle || !previewSummary) {
		return;
	}

	const refresh = () => {
		const title = titleInput.value.trim();
		const summary = summaryInput.value.trim();

		previewTitle.textContent = title || 'Your title appears here';
		previewSummary.textContent = summary || 'Your summary appears here.';

		if (previewCategory && categoryInput) {
			const label = categoryInput.options[categoryInput.selectedIndex]?.text || 'Category';
			previewCategory.textContent = label;
		}
	};

	titleInput.addEventListener('input', refresh);
	summaryInput.addEventListener('input', refresh);
	if (categoryInput) {
		categoryInput.addEventListener('change', refresh);
	}

	if (imageInput && previewImage) {
		imageInput.addEventListener('change', () => {
			const file = imageInput.files?.[0];
			if (!file) return;

			previewImage.src = URL.createObjectURL(file);
		});
	}

	refresh();
}

function initializeReadingTools() {
	const content = document.getElementById('postContent');
	if (!content) return;

	const buttons = [...document.querySelectorAll('[data-font-size]')];
	if (!buttons.length) {
		return;
	}

	const storageKey = 'bb-reading-size';
	const allowedSizes = new Set(['small', 'normal', 'large']);
	const presets = {
		small: { fontSize: '0.96rem', lineHeight: '1.7' },
		normal: { fontSize: '1.06rem', lineHeight: '1.85' },
		large: { fontSize: '1.22rem', lineHeight: '2' },
	};

	const applySize = (size) => {
		const safeSize = allowedSizes.has(size) ? size : 'normal';
		content.classList.remove('bb-reading-small', 'bb-reading-large');

		const preset = presets[safeSize];
		content.style.fontSize = preset.fontSize;
		content.style.lineHeight = preset.lineHeight;

		if (safeSize === 'small') {
			content.classList.add('bb-reading-small');
		} else if (safeSize === 'large') {
			content.classList.add('bb-reading-large');
		}

		buttons.forEach((button) => {
			const isActive = button.dataset.fontSize === safeSize;
			button.setAttribute('aria-pressed', String(isActive));
			button.classList.toggle('ring-2', isActive);
			button.classList.toggle('ring-cyan-300', isActive);
		});
	};

	const saved = localStorage.getItem(storageKey);
	applySize(saved ?? 'normal');

	buttons.forEach((button) => {
		if (button.dataset.readingBound === 'true') {
			return;
		}

		button.dataset.readingBound = 'true';
		button.addEventListener('click', () => {
			const size = button.dataset.fontSize;
			applySize(size);
			localStorage.setItem(storageKey, allowedSizes.has(size) ? size : 'normal');
		});
	});
}

function initializeDeletePrompts() {
	const modal = document.getElementById('deleteModal');
	const modalText = document.getElementById('deleteModalText');
	const confirmButton = document.getElementById('deleteModalConfirm');
	const closeButtons = document.querySelectorAll('[data-delete-close]');
	const triggers = document.querySelectorAll('[data-delete-trigger]');
	const modalPanel = modal?.querySelector('.bb-modal-panel');

	if (!modal || !modalText || !confirmButton || !triggers.length) {
		return;
	}

	// Hard reset to avoid stale state after refresh/navigation.
	modal.hidden = true;

	let activeForm = null;
	let lastFocused = null;

	const focusableSelector = [
		'a[href]',
		'button:not([disabled])',
		'textarea:not([disabled])',
		'input:not([disabled])',
		'select:not([disabled])',
		'[tabindex]:not([tabindex="-1"])',
	].join(',');

	const getFocusable = () => {
		if (!modalPanel) {
			return [];
		}

		return [...modalPanel.querySelectorAll(focusableSelector)];
	};

	const openModal = () => {
		lastFocused = document.activeElement;
		modal.hidden = false;
		const focusable = getFocusable();
		(focusable[0] || modalPanel)?.focus();
	};

	const closeModal = () => {
		modal.hidden = true;
		activeForm = null;
		if (lastFocused instanceof HTMLElement) {
			lastFocused.focus();
		}
	};

	triggers.forEach((trigger) => {
		trigger.addEventListener('click', () => {
			const formId = trigger.getAttribute('data-delete-form-id');
			const title = trigger.getAttribute('data-delete-title') || 'this post';
			const form = formId ? document.querySelector(`[data-delete-form="${formId}"]`) : null;

			if (!form) {
				return;
			}

			activeForm = form;
			modalText.textContent = `Delete "${title}"? This action cannot be undone.`;
			openModal();
		});
	});

	closeButtons.forEach((button) => {
		button.addEventListener('click', closeModal);
	});

	modal.addEventListener('click', (event) => {
		if (event.target === modal) {
			closeModal();
		}
	});

	confirmButton.addEventListener('click', () => {
		if (activeForm) {
			activeForm.submit();
		}
		closeModal();
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && !modal.hidden) {
			closeModal();
			return;
		}

		if (event.key !== 'Tab' || modal.hidden) {
			return;
		}

		const focusable = getFocusable();
		if (!focusable.length) {
			event.preventDefault();
			return;
		}

		const first = focusable[0];
		const last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	});
}

async function initializePageHero3D() {
	const canvases = document.querySelectorAll('[data-hero-3d]');

	if (!canvases.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	const THREE = await import('three');

	canvases.forEach((canvas) => {
		const variant = canvas.getAttribute('data-hero-3d') || 'about';
		const renderer = new THREE.WebGLRenderer({
			canvas,
			alpha: true,
			antialias: true,
		});
		renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

		const scene = new THREE.Scene();
		const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 70);
		camera.position.set(0, 0.2, 8.5);

		scene.add(new THREE.AmbientLight(0xc7f9ff, 0.65));

		const keyLight = new THREE.DirectionalLight(0xffffff, 0.85);
		keyLight.position.set(2.5, 2.5, 6);
		scene.add(keyLight);

		const edgeLight = new THREE.PointLight(variant === 'contact' ? 0xfbbf24 : 0x22d3ee, 1.2, 24);
		edgeLight.position.set(-3, 0.8, 4);
		scene.add(edgeLight);

		const lanePalette = variant === 'contact'
			? [0x22d3ee, 0x84cc16, 0xfbbf24, 0xf97316]
			: [0x22d3ee, 0x93c5fd, 0x84cc16, 0xa78bfa];

		const lanes = [];
		const laneY = [1.1, 0.45, -0.2, -0.9];

		for (let i = 0; i < 12; i += 1) {
			const color = lanePalette[i % lanePalette.length];
			const depth = -1.5 + ((i % 4) * 0.8);
			const width = 1.0 + ((i % 3) * 0.35);
			const height = 0.26 + ((i % 2) * 0.08);

			const capsule = new THREE.Mesh(
				new THREE.BoxGeometry(width, height, 0.18),
				new THREE.MeshStandardMaterial({
					color,
					emissive: 0x11223a,
					metalness: 0.25,
					roughness: 0.28,
				})
			);

			capsule.position.set(-10 - (i * 1.7), laneY[i % laneY.length], depth);
			capsule.rotation.y = (i % 2 === 0 ? 0.42 : -0.38);
			capsule.rotation.z = (i % 3 === 0 ? -0.2 : 0.18);
			scene.add(capsule);

			lanes.push({
				mesh: capsule,
				speed: 0.015 + ((i % 4) * 0.004),
				laneY: laneY[i % laneY.length],
				wobble: 0.16 + ((i % 3) * 0.03),
			});
		}

		const ring = new THREE.Mesh(
			new THREE.TorusGeometry(1.7, 0.035, 14, 96),
			new THREE.MeshBasicMaterial({
				color: variant === 'contact' ? 0xfbbf24 : 0x22d3ee,
				transparent: true,
				opacity: 0.45,
			})
		);
		ring.position.set(0.1, 0, -1.7);
		ring.rotation.x = 1.25;
		ring.rotation.y = 0.28;
		scene.add(ring);

		const core = new THREE.Mesh(
			new THREE.IcosahedronGeometry(0.38, 1),
			new THREE.MeshStandardMaterial({
				color: variant === 'contact' ? 0xf97316 : 0x22d3ee,
				emissive: 0x09223a,
				metalness: 0.35,
				roughness: 0.25,
				flatShading: true,
			})
		);
		core.position.set(0, 0, -1.4);
		scene.add(core);

		const resize = () => {
			const width = canvas.clientWidth;
			const height = canvas.clientHeight;
			if (!width || !height) {
				return;
			}

			renderer.setSize(width, height, false);
			camera.aspect = width / height;
			camera.updateProjectionMatrix();
		};

		resize();
		window.addEventListener('resize', resize);

		let t = 0;
		const animate = () => {
			t += 1;
			core.rotation.x += 0.014;
			core.rotation.y += 0.018;
			ring.rotation.z += 0.004;

			lanes.forEach((lane, index) => {
				lane.mesh.position.x += lane.speed;
				lane.mesh.position.y = lane.laneY + Math.sin((t * 0.01) + index) * lane.wobble;
				lane.mesh.rotation.y += 0.0025;

				if (lane.mesh.position.x > 10.5) {
					lane.mesh.position.x = -10.5;
				}
			});

			renderer.render(scene, camera);
			window.requestAnimationFrame(animate);
		};

		animate();
	});
}

function initializeTiltCards() {
	const cards = document.querySelectorAll('[data-tilt-card]');

	if (!cards.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	cards.forEach((card) => {
		const glare = card.querySelector('[data-tilt-glare]');

		card.addEventListener('mousemove', (event) => {
			const rect = card.getBoundingClientRect();
			const x = event.clientX - rect.left;
			const y = event.clientY - rect.top;
			const rotateX = ((y / rect.height) - 0.5) * -8;
			const rotateY = ((x / rect.width) - 0.5) * 12;

			card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;

			if (glare) {
				glare.style.opacity = '1';
				glare.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.5), rgba(255,255,255,0) 45%)`;
			}
		});

		card.addEventListener('mouseleave', () => {
			card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';

			if (glare) {
				glare.style.opacity = '0';
			}
		});
	});
}
