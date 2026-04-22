import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
	initializePwa();
	initializeCommentUpvoteForms();
	initializeCommentForms();
	initializeMobileNav();
	initializeBackNavigation();
	initializeMobileClickableCards();
	initializeThemeToggle();
	initializeTopicMap();
	initializePageHero3D();
	initializeTiltCards();
	initializePostPreview();
	initializePostToc();
	initializeReadingTools();
	initializeDeletePrompts();
	initializeBrainBot();
	initializeBackToTop();
	initializeKeyboardShortcuts();
	initializeCopyLinks();
	initializeRecentViews();
	initializeDraftAutosave();
	initializeActionFeedback();
	initializeReadingModeToggle();
	initializeInlineGlossary();
	initializeVoiceReader();
	initializePostFeedbackPoll();
	initializeParagraphBrainBot();
	initializePostChatbot();
	initializeFlashcards();
	initializeGlossaryPage();
	initializeRevisionMode();
	initializeReadingStreakWidgets();
	initializeDashboardPinboard();
	initializeCommentThreadToggles();

	document.body.classList.remove('bb-calm-focus');

function initializeBackNavigation() {
	const backButtons = document.querySelectorAll('[data-back-nav]');

	if (!backButtons.length) {
		return;
	}

	backButtons.forEach((button) => {
		button.addEventListener('click', () => {
			const fallbackUrl = button.getAttribute('data-fallback-url') || '/';

			if (window.history.length > 1) {
				window.history.back();
				return;
			}

			window.location.href = fallbackUrl;
		});
	});

	function initializeCommentThreadToggles() {
		const toggleButtons = document.querySelectorAll('[data-replies-toggle]');

		if (!toggleButtons.length) {
			return;
		}

		toggleButtons.forEach((button) => {
			button.addEventListener('click', () => {
				const targetId = button.getAttribute('data-target');
				if (!targetId) {
					return;
				}

				const target = document.getElementById(targetId);
				if (!(target instanceof HTMLElement)) {
					return;
				}

				const expandLabel = button.getAttribute('data-expand-label') || 'Show more replies';
				const collapseLabel = button.getAttribute('data-collapse-label') || 'Show fewer replies';
				const willExpand = target.hidden;

				target.hidden = !willExpand;
				button.textContent = willExpand ? collapseLabel : expandLabel;
				button.setAttribute('aria-expanded', willExpand ? 'true' : 'false');
			});
		});
	}
}
	localStorage.removeItem('bb-calm-focus');

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

function initializePostToc() {
	const tocLinks = [...document.querySelectorAll('.bb-toc-link')];
	const progressBar = document.querySelector('[data-toc-progress-bar]');
	const progressLabel = document.querySelector('[data-toc-progress-label]');

	if (!tocLinks.length) {
		return;
	}

	const sections = tocLinks
		.map((link) => {
			const href = link.getAttribute('href') || '';
			if (!href.startsWith('#')) {
				return null;
			}

			const target = document.querySelector(href);
			if (!(target instanceof HTMLElement)) {
				return null;
			}

			return { link, target };
		})
		.filter(Boolean);

	if (!sections.length) {
		return;
	}

	const updateActive = (activeId) => {
		let activeIndex = 0;

		sections.forEach((entry, index) => {
			const isActive = entry.target.id === activeId;
			entry.link.classList.toggle('is-active', isActive);
			entry.link.setAttribute('aria-current', isActive ? 'location' : 'false');

			if (isActive) {
				activeIndex = index;
			}
		});

		if (progressBar instanceof HTMLElement) {
			const percent = ((activeIndex + 1) / sections.length) * 100;
			progressBar.style.width = `${percent}%`;
		}

		if (progressLabel instanceof HTMLElement) {
			progressLabel.textContent = `Section ${activeIndex + 1} of ${sections.length}`;
		}
	};

	tocLinks.forEach((link) => {
		link.addEventListener('click', (event) => {
			const href = link.getAttribute('href') || '';
			if (!href.startsWith('#')) {
				return;
			}

			const target = document.querySelector(href);
			if (!(target instanceof HTMLElement)) {
				return;
			}

			event.preventDefault();
			target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			history.replaceState(null, '', href);
			updateActive(target.id);
		});
	});

	if ('IntersectionObserver' in window) {
		const observer = new IntersectionObserver(
			(entries) => {
				const visible = entries
					.filter((entry) => entry.isIntersecting)
					.sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

				if (visible?.target instanceof HTMLElement) {
					updateActive(visible.target.id);
				}
			},
			{
				rootMargin: '-20% 0px -58% 0px',
				threshold: [0.1, 0.25, 0.5, 0.75],
			}
		);

		sections.forEach((entry) => observer.observe(entry.target));
	} else {
		const onScroll = () => {
			let activeId = sections[0].target.id;

			sections.forEach((entry) => {
				if (entry.target.getBoundingClientRect().top <= 140) {
					activeId = entry.target.id;
				}
			});

			updateActive(activeId);
		};

		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
		return;
	}

	const initialHash = window.location.hash;
	if (initialHash) {
		const active = sections.find((entry) => `#${entry.target.id}` === initialHash);
		if (active) {
			updateActive(active.target.id);
			return;
		}
	}

	updateActive(sections[0].target.id);
}

function initializeCommentUpvoteForms() {
	if (window.__bbCommentUpvoteBound) {
		return;
	}

	window.__bbCommentUpvoteBound = true;

	document.addEventListener('submit', async (event) => {
		const target = event.target;
		if (!(target instanceof HTMLFormElement) || !target.matches('[data-comment-upvote-form]')) {
			return;
		}

		event.preventDefault();

		const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
		if (!csrfToken) {
			target.submit();
			return;
		}

		const button = target.querySelector('[data-comment-upvote-button]');
		const container = target.parentElement;
		const count = container?.querySelector('[data-comment-upvote-count]');

		if (!(button instanceof HTMLButtonElement)) {
			target.submit();
			return;
		}

		button.disabled = true;

		try {
			const response = await fetch(target.action, {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
				},
			});

			if (!response.ok) {
				throw new Error('Vote request failed');
			}

			const data = await response.json();
			const isUpvoted = Boolean(data.upvoted);
			const upvotes = Number(data.upvotes ?? 0);

			button.dataset.upvoted = isUpvoted ? '1' : '0';
			button.textContent = isUpvoted ? 'Upvoted' : 'Upvote helpful';
			button.classList.toggle('bb-comment-upvote-active', isUpvoted);

			if (count instanceof HTMLElement) {
				count.textContent = `${upvotes} ${upvotes === 1 ? 'upvote' : 'upvotes'}`;
			}

			if (typeof showToast === 'function' && typeof data.message === 'string') {
				showToast(data.message);
			}
		} catch {
			if (typeof showToast === 'function') {
				showToast('Could not update upvote right now.');
			}
		} finally {
			button.disabled = false;
		}
	}, true);
}

function initializeCommentForms() {
	if (window.__bbCommentFormsBound) {
		return;
	}

	window.__bbCommentFormsBound = true;

	document.addEventListener('submit', async (event) => {
		const target = event.target;
		if (!(target instanceof HTMLFormElement) || !target.matches('[data-comment-form]')) {
			return;
		}

		event.preventDefault();

		const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
		if (!csrfToken) {
			target.submit();
			return;
		}

		const submitButton = target.querySelector('button[type="submit"]');
		if (submitButton instanceof HTMLButtonElement) {
			submitButton.disabled = true;
		}

		const formData = new FormData(target);
		const bodyField = target.querySelector('textarea[name="body"]');

		try {
			const response = await fetch(target.action, {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
				},
				body: formData,
			});

			const data = await response.json();
			if (!response.ok || !data?.comment) {
				throw new Error(data?.message || 'Comment submission failed');
			}

			if (bodyField instanceof HTMLTextAreaElement) {
				bodyField.value = '';
			}

			const commentEl = createInlineCommentElement(data.comment);
			const parentCommentId = Number(data.comment.parent_comment_id || 0);

			if (parentCommentId > 0) {
				const parentArticle = document.querySelector(`[data-comment-id="${parentCommentId}"]`);
				if (parentArticle instanceof HTMLElement) {
					let repliesRoot = parentArticle.querySelector('[data-comment-replies-root]');
					if (!(repliesRoot instanceof HTMLElement)) {
						repliesRoot = document.createElement('div');
						repliesRoot.className = 'mt-4 space-y-4 border-l-2 border-slate-200 pl-4';
						repliesRoot.setAttribute('data-comment-replies-root', '');
						parentArticle.appendChild(repliesRoot);
					}

					repliesRoot.prepend(commentEl);
				}
			} else {
				const commentsSection = document.getElementById('comments-section');
				const rootList = commentsSection?.querySelector('[data-comments-root-list]');
				const sortMode = commentsSection?.getAttribute('data-comments-sort') || 'top';

				if (rootList instanceof HTMLElement) {
					if (sortMode === 'new') {
						rootList.prepend(commentEl);
					} else {
						rootList.appendChild(commentEl);
					}
				}
			}

			const commentsTotal = document.querySelector('[data-comments-total]');
			if (commentsTotal instanceof HTMLElement) {
				const current = Number((commentsTotal.textContent || '0').replace(/\D+/g, '') || 0);
				commentsTotal.textContent = `${current + 1} total`;
			}

			if (typeof showToast === 'function' && typeof data.message === 'string') {
				showToast(data.message);
			}
		} catch {
			target.submit();
		} finally {
			if (submitButton instanceof HTMLButtonElement) {
				submitButton.disabled = false;
			}
		}
	}, true);
}

function createInlineCommentElement(comment) {
	const article = document.createElement('article');
	article.className = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm';
	article.setAttribute('data-comment-id', String(comment.id));

	const name = String(comment.user?.name || 'User');
	const avatar = String(comment.user?.profile_photo_url || '');
	const body = String(comment.body || '');
	const createdAt = String(comment.created_at_human || 'just now');

	article.innerHTML = `
		<div class="flex items-start justify-between gap-3">
			<div class="flex items-center gap-3">
				<img src="${avatar}" alt="${name}" class="h-10 w-10 rounded-full border border-slate-200 object-cover">
				<div>
					<p class="font-semibold text-slate-900">${name}</p>
					<p class="text-xs text-slate-500">${createdAt}</p>
				</div>
			</div>
		</div>
		<p class="mt-3 whitespace-pre-wrap text-sm text-slate-700"></p>
	`;

	const bodyEl = article.querySelector('p.mt-3');
	if (bodyEl instanceof HTMLElement) {
		bodyEl.textContent = body;
	}

	return article;
}

function initializeMobileClickableCards() {
	const cards = document.querySelectorAll('[data-mobile-card-link]');

	if (!cards.length || !window.matchMedia('(max-width: 767px)').matches) {
		return;
	}

	const interactiveSelector = 'a, button, input, textarea, select, label, form, [data-copy-url], [data-delete-trigger]';

	cards.forEach((card) => {
		if (!(card instanceof HTMLElement)) {
			return;
		}

		card.classList.add('bb-card-tap-target');

		card.addEventListener('click', (event) => {
			const target = event.target;
			if (!(target instanceof Element)) {
				return;
			}

			if (target.closest(interactiveSelector)) {
				return;
			}

			const url = card.getAttribute('data-mobile-card-link');
			if (!url) {
				return;
			}

			window.location.href = url;
		});
	});
}

function initializePwa() {
	if (!('serviceWorker' in navigator)) {
		return;
	}

	const isLocalhost = ['localhost', '127.0.0.1'].includes(window.location.hostname);

	if (isLocalhost) {
		navigator.serviceWorker.getRegistrations().then((registrations) => {
			registrations.forEach((registration) => registration.unregister());
		});

		if ('caches' in window) {
			caches.keys().then((keys) => {
				keys.forEach((key) => caches.delete(key));
			});
		}

		return;
	}

	window.addEventListener('load', () => {
		navigator.serviceWorker.register('/sw.js').catch(() => {
			// Ignore registration failures to avoid blocking app interactions.
		});
	});
}

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

function supportsSpeechSynthesis() {
	return 'speechSynthesis' in window;
}

function pickPreferredVoice(synth, preferredLang = 'en-US') {
	if (!synth || typeof synth.getVoices !== 'function') {
		return null;
	}

	const voices = synth.getVoices();
	if (!Array.isArray(voices) || !voices.length) {
		return null;
	}

	const lang = String(preferredLang || 'en-US').toLowerCase();
	const baseLang = lang.split('-')[0];
	const qualityHints = [
		'natural',
		'neural',
		'premium',
		'wavenet',
		'aria',
		'jenny',
		'guy',
		'davis',
		'aria online',
		'google us english',
		'samantha',
		'daniel',
	];

	const scoreVoice = (voice) => {
		const voiceLang = String(voice.lang || '').toLowerCase();
		const name = String(voice.name || '').toLowerCase();
		let score = 0;

		if (voiceLang === lang) {
			score += 120;
		} else if (voiceLang.startsWith(`${baseLang}-`)) {
			score += 90;
		} else if (voiceLang.startsWith(baseLang)) {
			score += 70;
		}

		if (voice.default) {
			score += 30;
		}

		if (voice.localService) {
			score += 15;
		}

		qualityHints.forEach((hint, index) => {
			if (name.includes(hint)) {
				score += 25 - Math.min(index, 10);
			}
		});

		if (name.includes('female') || name.includes('woman')) {
			score += 4;
		}

		return score;
	};

	return [...voices].sort((a, b) => scoreVoice(b) - scoreVoice(a))[0] || null;
}

function createHumanLikeUtterance(text, synth, preferredLang = 'en-US') {
	const utterance = new SpeechSynthesisUtterance(text);
	const voice = pickPreferredVoice(synth, preferredLang);

	utterance.lang = preferredLang;
	utterance.rate = 0.95;
	utterance.pitch = 1.03;
	utterance.volume = 1;

	if (voice) {
		utterance.voice = voice;
		utterance.lang = voice.lang || preferredLang;
	}

	return utterance;
}

function attachAnswerVoiceControl(container, text) {
	if (!(container instanceof HTMLElement)) return;
	if (!supportsSpeechSynthesis()) return;

	const trimmed = String(text || '').trim();
	if (!trimmed) return;

	const controls = document.createElement('div');
	controls.className = 'bb-answer-voice';

	const toggle = document.createElement('button');
	toggle.type = 'button';
	toggle.className = 'bb-button-secondary bb-voice-chip';
	toggle.textContent = 'Start';

	const stop = document.createElement('button');
	stop.type = 'button';
	stop.className = 'bb-button-secondary bb-voice-chip';
	stop.textContent = 'Stop';
	stop.disabled = true;

	let active = false;
	let paused = false;
	const synth = window.speechSynthesis;
	let preferredVoice = pickPreferredVoice(synth, 'en-US');

	const refreshPreferredVoice = () => {
		preferredVoice = pickPreferredVoice(synth, 'en-US');
	};

	refreshPreferredVoice();
	synth.addEventListener('voiceschanged', refreshPreferredVoice);

	const splitText = (value, max = 220) => {
		const chunks = [];
		let remaining = value.replace(/\s+/g, ' ').trim();

		while (remaining.length > max) {
			let cut = remaining.lastIndexOf('. ', max);
			if (cut < max * 0.5) {
				cut = remaining.lastIndexOf(' ', max);
			}
			if (cut <= 0) {
				cut = max;
			}

			chunks.push(remaining.slice(0, cut + 1).trim());
			remaining = remaining.slice(cut + 1).trim();
		}

		if (remaining) {
			chunks.push(remaining);
		}

		return chunks.filter(Boolean);
	};

	const setControls = () => {
		toggle.textContent = !active ? 'Start' : (paused ? 'Resume' : 'Pause');
		stop.disabled = !active;
	};

	const stopReading = () => {
		synth.cancel();
		active = false;
		paused = false;
		setControls();
	};

	const startReading = () => {
		synth.cancel();

		const chunks = splitText(trimmed);
		if (!chunks.length) return;

		active = true;
		paused = false;
		setControls();

		let remaining = chunks.length;
		chunks.forEach((chunk) => {
			const utterance = createHumanLikeUtterance(chunk, synth, 'en-US');
			if (preferredVoice) {
				utterance.voice = preferredVoice;
				utterance.lang = preferredVoice.lang || utterance.lang;
			}

			utterance.onend = () => {
				remaining -= 1;
				if (remaining <= 0 && active && !paused) {
					active = false;
					setControls();
				}
			};

			utterance.onerror = () => {
				stopReading();
			};

			synth.speak(utterance);
		});
	};

	toggle.addEventListener('click', () => {
		if (!active) {
			startReading();
			return;
		}

		if (paused) {
			synth.resume();
			paused = false;
			setControls();
		} else {
			synth.pause();
			paused = true;
			setControls();
		}
	});

	stop.addEventListener('click', () => {
		stopReading();
	});

	controls.appendChild(toggle);
	controls.appendChild(stop);
	container.appendChild(controls);

	window.addEventListener('beforeunload', () => {
		synth.removeEventListener('voiceschanged', refreshPreferredVoice);
	});
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

		if (role === 'bot') {
			attachAnswerVoiceControl(bubble, text);
		}

		messages.appendChild(bubble);
		messages.scrollTop = messages.scrollHeight;
		return bubble;
	};

	const addFollowups = (anchor, question, answer) => {
		if (!(anchor instanceof HTMLElement)) return;

		const topic = (question || answer || '').split(/[.!?]/)[0]?.slice(0, 70)?.trim() || 'this topic';
		const suggestions = [
			`Can you explain ${topic} in simpler words?`,
			`Give me one real-world example of ${topic}.`,
			`Quiz me on ${topic} with 3 questions.`,
		];

		const wrap = document.createElement('div');
		wrap.className = 'bb-followup-wrap';
		const title = document.createElement('p');
		title.className = 'bb-followup-title';
		title.textContent = 'Suggested follow-ups';
		wrap.appendChild(title);

		suggestions.forEach((suggestion) => {
			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'bb-followup-chip';
			button.textContent = suggestion;
			button.addEventListener('click', () => {
				input.value = suggestion;
				input.focus();
			});
			wrap.appendChild(button);
		});

		anchor.appendChild(wrap);
	};

	const addHistoryPair = (question, answer) => {
		if (question) {
			addMessage(question, 'user');
		}
		if (answer) {
			const bubble = addMessage(answer, 'bot');
			addFollowups(bubble, question, answer);
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

			const answer = data.answer || 'I could not generate an answer yet.';
			const bubble = addMessage(answer, 'bot');
			addFollowups(bubble, message, answer);

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
	const root = document.documentElement;
	const toggles = [...document.querySelectorAll('[data-theme-toggle]')];
	const key = 'bb-theme';

	const applyTheme = (theme) => {
		const dark = theme === 'dark';
		root.classList.toggle('dark', dark);
		document.body.classList.toggle('theme-dark', dark); // Keep this for existing custom CSS
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
			const next = root.classList.contains('dark') ? 'light' : 'dark';
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

function initializeReadingModeToggle() {
	const toggle = document.getElementById('readingModeToggle');
	if (!toggle) return;

	if (toggle.dataset.readingModeBound === 'true') {
		return;
	}

	toggle.dataset.readingModeBound = 'true';

	const key = 'bb-reading-mode';
	const sidebar = document.getElementById('postSidebar');

	const setMode = (enabled) => {
		document.body.classList.toggle('bb-reading-mode', enabled);
		if (sidebar instanceof HTMLElement) {
			sidebar.hidden = enabled;
		}
		toggle.setAttribute('aria-pressed', String(enabled));
		toggle.textContent = enabled ? 'Exit reading mode' : 'Reading mode';
		localStorage.setItem(key, enabled ? 'on' : 'off');
	};

	setMode(localStorage.getItem(key) === 'on');

	toggle.addEventListener('click', () => {
		setMode(!document.body.classList.contains('bb-reading-mode'));
	});
}

function initializeInlineGlossary() {
	const content = document.getElementById('postContent');
	if (!content) return;

	if (content.dataset.glossaryBound === 'true') {
		return;
	}

	content.dataset.glossaryBound = 'true';

	const terms = {
		api: 'An API is a defined way for software systems to communicate and exchange data.',
		algorithm: 'A step-by-step method used to solve a problem or perform a computation.',
		app: 'An app is a software application designed to perform specific tasks for users.',
		closure: 'A function that keeps access to variables from its outer scope, even after that scope finishes.',
		cache: 'A temporary storage layer that keeps frequently used data for faster access.',
		server: 'A server is a system that provides data or services to other systems over a network.',
		function: 'A function is a reusable block of code that performs a specific task.',
		model: 'A model is a simplified representation used to explain, predict, or structure data and behavior.',
		data: 'Data is information that can be stored, processed, and analyzed.',
		database: 'An organized system for storing and querying structured information.',
		authentication: 'The process of verifying who a user is before granting access.',
		encryption: 'A method that transforms readable data into unreadable form to protect it.',
	};

	const dictionary = new Map(Object.entries(terms).map(([term, def]) => [term.toLowerCase(), def]));
	const keys = [...dictionary.keys()];
	if (!keys.length) return;

	const escaped = keys.map((term) => term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
	const regex = new RegExp(`\\b(${escaped.join('|')})\\b`, 'gi');

	const walker = document.createTreeWalker(content, NodeFilter.SHOW_TEXT);
	const textNodes = [];

	while (walker.nextNode()) {
		const node = walker.currentNode;
		const parent = node.parentElement;
		if (!parent) continue;

		if (parent.closest('a, code, pre, script, style, .bb-glossary-term')) {
			continue;
		}

		regex.lastIndex = 0;
		if (regex.test(node.nodeValue || '')) {
			textNodes.push(node);
		}
	}

	textNodes.forEach((node) => {
		const text = node.nodeValue || '';
		regex.lastIndex = 0;
		if (!regex.test(text)) {
			return;
		}

		const fragment = document.createDocumentFragment();
		let lastIndex = 0;
		regex.lastIndex = 0;

		for (const match of text.matchAll(regex)) {
			const start = match.index ?? 0;
			const end = start + match[0].length;

			if (start > lastIndex) {
				fragment.appendChild(document.createTextNode(text.slice(lastIndex, start)));
			}

			const key = match[0].toLowerCase();
			const definition = dictionary.get(key) || '';

			const term = document.createElement('span');
			term.className = 'bb-glossary-term';
			term.tabIndex = 0;
			term.textContent = match[0];
			term.setAttribute('data-glossary-term', match[0]);

			const tip = document.createElement('span');
			tip.className = 'bb-glossary-tip';
			tip.textContent = definition;
			term.appendChild(tip);

			fragment.appendChild(term);
			lastIndex = end;
		}

		if (lastIndex < text.length) {
			fragment.appendChild(document.createTextNode(text.slice(lastIndex)));
		}

		node.parentNode?.replaceChild(fragment, node);
	});

	const glossaryStorageKey = 'bb-learned-terms';
	content.querySelectorAll('[data-glossary-term]').forEach((termElement) => {
		if (!(termElement instanceof HTMLElement)) return;

		termElement.addEventListener('click', () => {
			const term = (termElement.getAttribute('data-glossary-term') || '').trim();
			const definition = termElement.querySelector('.bb-glossary-tip')?.textContent || '';
			if (!term || !definition) return;

			let list = [];
			try {
				list = JSON.parse(localStorage.getItem(glossaryStorageKey) || '[]');
			} catch {
				list = [];
			}

			const normalized = String(term).toLowerCase();
			const next = Array.isArray(list) ? list.filter((item) => String(item.term || '').toLowerCase() !== normalized) : [];
			next.unshift({
				term,
				definition,
				saved_at: new Date().toISOString(),
			});

			localStorage.setItem(glossaryStorageKey, JSON.stringify(next.slice(0, 80)));
			showToast(`Saved term: ${term}`);
		});
	});
}

function initializeParagraphBrainBot() {
	const content = document.getElementById('postContent');
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
	if (!content || !csrfToken) return;

	const paragraphs = [...content.querySelectorAll('p')];
	if (!paragraphs.length) return;

	paragraphs.forEach((paragraph) => {
		if (!(paragraph instanceof HTMLElement) || paragraph.dataset.askBound === 'true') {
			return;
		}

		paragraph.dataset.askBound = 'true';

		const controls = document.createElement('div');
		controls.className = 'bb-inline-tools';
        controls.style.columnGap = '1rem';
        controls.style.rowGap = '0.85rem';

		const ask = document.createElement('button');
		ask.type = 'button';
		ask.className = 'bb-button bb-inline-strong-button';
		ask.textContent = 'Explain Simpler';
		ask.setAttribute('aria-label', 'Explain this paragraph in simpler words');

		const pin = document.createElement('button');
		pin.type = 'button';
		pin.className = 'bb-button-secondary bb-inline-strong-button';
		pin.textContent = 'Pin takeaway';
		pin.setAttribute('aria-label', 'Pin this paragraph as a takeaway');
        pin.style.marginLeft = '1rem';

		const output = document.createElement('div');
		output.className = 'bb-inline-answer';
		output.hidden = true;

		ask.addEventListener('click', async () => {
			const contextText = (paragraph.textContent || '').trim();
			if (!contextText) return;

			ask.disabled = true;
			output.hidden = false;
			output.textContent = 'Asking brainBot...';

			try {
				const response = await fetch('/brainbot/chat', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': csrfToken,
						'Accept': 'application/json',
					},
					body: JSON.stringify({
						message: `Explain this paragraph in simpler words:\n\n${contextText}`,
					}),
				});

				const data = await response.json();
				if (!response.ok) {
					output.textContent = 'Could not fetch simplified explanation right now.';
					return;
				}

				output.textContent = data.answer || 'No explanation returned.';
			} catch {
				output.textContent = 'Could not reach brainBot right now.';
			} finally {
				ask.disabled = false;
			}
		});

		pin.addEventListener('click', () => {
			const text = (paragraph.textContent || '').trim();
			if (!text) return;

			const marker = document.querySelector('[data-recent-view-post]');
			const title = marker?.getAttribute('data-title') || 'Post';
			const url = marker?.getAttribute('data-url') || window.location.href;

			let saved = [];
			try {
				saved = JSON.parse(localStorage.getItem('bb-takeaways') || '[]');
			} catch {
				saved = [];
			}

			const next = Array.isArray(saved)
				? saved.filter((item) => !(item.text === text && item.url === url))
				: [];

			next.unshift({ text, title, url, created_at: new Date().toISOString() });
			localStorage.setItem('bb-takeaways', JSON.stringify(next.slice(0, 5)));
			showToast('Pinned to key takeaways.');
		});

		controls.appendChild(ask);
		controls.appendChild(pin);
		paragraph.insertAdjacentElement('afterend', controls);
		controls.insertAdjacentElement('afterend', output);
	});
}

function initializeFlashcards() {
	const panel = document.getElementById('flashcardsPanel');
	const modal = document.getElementById('flashcardsModal');
	const open = document.getElementById('openFlashcardsModal');
	const deck = document.getElementById('flashcardsDeck');
	const generate = document.getElementById('generateFlashcards');
	const save = document.getElementById('saveFlashcards');
	const content = document.getElementById('postContent');
	if (!panel || !modal || !open || !deck || !generate || !save || !content) return;

	if (panel.dataset.flashcardsBound === 'true') {
		return;
	}

	panel.dataset.flashcardsBound = 'true';

	const categorySlug = content.getAttribute('data-post-category-slug') || 'general';
	const storageKey = `bb-flashcards-${categorySlug}`;
	let currentCards = [];
	let index = 0;
	let showAnswer = false;
	let touchStartX = 0;
	let lastFocused = null;

	const closeButtons = [...modal.querySelectorAll('[data-flashcards-close]')];
	const modalPanel = modal.querySelector('.bb-modal-panel');

	const openModal = () => {
		lastFocused = document.activeElement;
		modal.hidden = false;
		if (modalPanel instanceof HTMLElement) {
			modalPanel.focus();
		}
	};

	const closeModal = () => {
		modal.hidden = true;
		if (lastFocused instanceof HTMLElement) {
			lastFocused.focus();
		}
	};

	open.addEventListener('click', openModal);
	closeButtons.forEach((button) => {
		button.addEventListener('click', closeModal);
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && !modal.hidden) {
			closeModal();
		}
	});

	const render = () => {
		if (!currentCards.length) {
			deck.innerHTML = '<p class="text-sm text-slate-600">Generate cards to start studying.</p>';
			save.disabled = true;
			return;
		}

		const card = currentCards[index];
		const body = showAnswer
			? `<p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">Answer</p><p class="mt-2 text-sm text-slate-700">${escapeHtml(card.a)}</p>`
			: `<p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">Question</p><p class="mt-2 text-sm font-semibold text-slate-900">${escapeHtml(card.q)}</p><p class="mt-3 text-xs text-slate-500">Tap card to reveal answer</p>`;

		deck.innerHTML = `<div class="bb-flashcard-wrap"><article class="bb-flashcard" id="flashcardActive" role="button" tabindex="0" aria-label="Flashcard">${body}</article><div class="bb-flashcard-controls"><button type="button" class="bb-button-secondary" id="flashPrev">Prev</button><p class="text-xs font-semibold text-slate-600">${index + 1} / ${currentCards.length}</p><button type="button" class="bb-button-secondary" id="flashNext">Next</button></div></div>`;

		const active = document.getElementById('flashcardActive');
		const prev = document.getElementById('flashPrev');
		const next = document.getElementById('flashNext');

		const toggleAnswer = () => {
			showAnswer = !showAnswer;
			render();
		};

		if (active) {
			active.addEventListener('click', toggleAnswer);
			active.addEventListener('keydown', (event) => {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					toggleAnswer();
				}
			});

			active.addEventListener('touchstart', (event) => {
				touchStartX = event.touches[0]?.clientX || 0;
			}, { passive: true });

			active.addEventListener('touchend', (event) => {
				const endX = event.changedTouches[0]?.clientX || 0;
				const delta = endX - touchStartX;

				if (Math.abs(delta) < 45) {
					return;
				}

				if (delta < 0) {
					index = (index + 1) % currentCards.length;
				} else {
					index = (index - 1 + currentCards.length) % currentCards.length;
				}

				showAnswer = false;
				render();
			}, { passive: true });
		}

		if (prev) {
			prev.addEventListener('click', () => {
				index = (index - 1 + currentCards.length) % currentCards.length;
				showAnswer = false;
				render();
			});
		}

		if (next) {
			next.addEventListener('click', () => {
				index = (index + 1) % currentCards.length;
				showAnswer = false;
				render();
			});
		}

		save.disabled = false;
	};

	const paragraphs = () => [...content.querySelectorAll('p')]
		.map((p) => (p.textContent || '').trim())
		.filter(Boolean);

	const createCards = () => {
		const lines = paragraphs().slice(0, 6);
		return lines.map((line, index) => {
			const words = line.split(/\s+/).filter(Boolean);
			const topic = words.slice(0, 7).join(' ');
			return {
				q: `What is the key idea in part ${index + 1}: "${topic}"?`,
				a: line,
			};
		});
	};

	generate.addEventListener('click', () => {
		currentCards = createCards();
		index = 0;
		showAnswer = false;
		render();
		showToast('Flashcards generated.');
	});

	save.addEventListener('click', () => {
		if (!currentCards.length) return;
		localStorage.setItem(storageKey, JSON.stringify(currentCards));
		showToast('Flashcard deck saved for this category.');
	});

	try {
		const existing = JSON.parse(localStorage.getItem(storageKey) || '[]');
		if (Array.isArray(existing) && existing.length) {
			currentCards = existing;
			index = 0;
			showAnswer = false;
			render();
		}
	} catch {
		render();
	}
}

function initializePostChatbot() {
	const form = document.getElementById('postChatForm');
	const input = document.getElementById('postChatInput');
	const output = document.getElementById('postChatAnswer');
	const content = document.getElementById('postContent');
	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	if (!form || !input || !output || !content || !csrfToken) {
		return;
	}

	if (form.dataset.postChatBound === 'true') {
		return;
	}

	form.dataset.postChatBound = 'true';

	form.addEventListener('submit', async (event) => {
		event.preventDefault();

		const question = input.value.trim();
		if (!question) {
			return;
		}

		const title = content.getAttribute('data-post-title') || 'Post';
		const summary = ([...content.querySelectorAll('p')].map((p) => (p.textContent || '').trim()).filter(Boolean).slice(0, 3).join(' ')).slice(0, 900);

		output.hidden = false;
		output.textContent = 'Asking chatbot...';
		input.disabled = true;

		try {
			const response = await fetch('/brainbot/chat', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					message: `Use this post context to answer: ${title}. Context: ${summary}. Question: ${question}`,
				}),
			});

			const data = await response.json();
			if (!response.ok) {
				output.textContent = 'Could not get an answer right now. Please try again.';
				return;
			}

			const answer = data.answer || 'No answer returned.';
			output.textContent = answer;
			attachAnswerVoiceControl(output, answer);
		} catch {
			output.textContent = 'Could not reach chatbot right now.';
		} finally {
			input.disabled = false;
			input.focus();
		}
	});
}

function initializeGlossaryPage() {
	const list = document.getElementById('learnedTermsList');
	const empty = document.getElementById('learnedTermsEmpty');
	const clear = document.getElementById('clearLearnedTerms');
	if (!list || !empty || !clear) return;

	const key = 'bb-learned-terms';

	const render = () => {
		let items = [];
		try {
			items = JSON.parse(localStorage.getItem(key) || '[]');
		} catch {
			items = [];
		}

		if (!Array.isArray(items) || !items.length) {
			list.innerHTML = '';
			empty.hidden = false;
			return;
		}

		empty.hidden = true;
		list.innerHTML = items.slice(0, 60).map((item) => (
			`<article class="rounded-xl border border-slate-200 bg-white p-3"><p class="text-sm font-semibold text-slate-900">${escapeHtml(item.term || '')}</p><p class="mt-1 text-xs text-slate-600">${escapeHtml(item.definition || '')}</p></article>`
		)).join('');
	};

	clear.addEventListener('click', () => {
		localStorage.removeItem(key);
		render();
		showToast('Learned terms cleared.');
	});

	render();
}

function initializeRevisionMode() {
	const output = document.getElementById('revisionOutput');
	const content = document.getElementById('postContent');
	const buttons = [...document.querySelectorAll('[data-revision-mode]')];
	if (!output || !content || !buttons.length) return;

	const lines = [...content.querySelectorAll('p')]
		.map((p) => (p.textContent || '').trim())
		.filter(Boolean)
		.slice(0, 8);

	const setBullets = () => {
		output.innerHTML = `<ul class="list-disc pl-5">${lines.map((line) => `<li>${escapeHtml(line)}</li>`).join('')}</ul>`;
	};

	const setQuestions = () => {
		output.innerHTML = lines.map((line, index) => {
			const stem = line.split(/[,.]/)[0] || line;
			return `<p class="mb-2"><strong>Q${index + 1}.</strong> Explain: ${escapeHtml(stem)}?</p>`;
		}).join('');
	};

	const setCheat = () => {
		output.innerHTML = lines.map((line, index) => `<p class="mb-2"><strong>${index + 1}.</strong> ${escapeHtml(line.slice(0, 180))}</p>`).join('');
	};

	buttons.forEach((button) => {
		button.addEventListener('click', () => {
			const mode = button.getAttribute('data-revision-mode');
			if (mode === 'bullets') {
				setBullets();
			} else if (mode === 'questions') {
				setQuestions();
			} else {
				setCheat();
			}
		});
	});
}

function initializeReadingStreakWidgets() {
	const marker = document.querySelector('[data-recent-view-post]');
	const key = 'bb-reading-activity';

	let activity = { dates: [], weeklyGoal: 5 };
	try {
		const raw = JSON.parse(localStorage.getItem(key) || '{}');
		activity = {
			dates: Array.isArray(raw.dates) ? raw.dates : [],
			weeklyGoal: Number(raw.weeklyGoal) > 0 ? Number(raw.weeklyGoal) : 5,
		};
	} catch {
		activity = { dates: [], weeklyGoal: 5 };
	}

	if (marker) {
		const today = new Date().toISOString().slice(0, 10);
		if (!activity.dates.includes(today)) {
			activity.dates.unshift(today);
			activity.dates = activity.dates.slice(0, 180);
		}
	}

	const streak = computeStreak(activity.dates);
	const weekCount = countThisWeek(activity.dates);

	const weeklyInput = document.getElementById('weeklyGoalInput');
	const streakText = document.getElementById('streakText');
	const weeklyGoalText = document.getElementById('weeklyGoalText');
	const weeklyGoalBar = document.getElementById('weeklyGoalBar');
	const dashboardStreakText = document.getElementById('dashboardStreakText');
	const dashboardGoalText = document.getElementById('dashboardGoalText');
	const dashboardGoalBar = document.getElementById('dashboardGoalBar');

	const render = () => {
		const pct = Math.max(0, Math.min(100, Math.round((weekCount / activity.weeklyGoal) * 100)));
		if (streakText) streakText.textContent = `Streak: ${streak} day${streak === 1 ? '' : 's'}`;
		if (weeklyGoalText) weeklyGoalText.textContent = `${weekCount} / ${activity.weeklyGoal} posts this week`;
		if (weeklyGoalBar) weeklyGoalBar.style.width = `${pct}%`;
		if (dashboardStreakText) dashboardStreakText.textContent = `Streak: ${streak} day${streak === 1 ? '' : 's'}`;
		if (dashboardGoalText) dashboardGoalText.textContent = `${weekCount} / ${activity.weeklyGoal} posts this week`;
		if (dashboardGoalBar) dashboardGoalBar.style.width = `${pct}%`;
	};

	if (weeklyInput) {
		weeklyInput.value = String(activity.weeklyGoal);
		weeklyInput.addEventListener('change', () => {
			const goal = Math.max(1, Math.min(21, Number(weeklyInput.value) || 5));
			activity.weeklyGoal = goal;
			localStorage.setItem(key, JSON.stringify(activity));
			render();
		});
	}

	localStorage.setItem(key, JSON.stringify(activity));
	render();
}

function initializeDashboardPinboard() {
	const list = document.getElementById('takeawaysList');
	const empty = document.getElementById('takeawaysEmpty');
	if (!list || !empty) return;

	let items = [];
	try {
		items = JSON.parse(localStorage.getItem('bb-takeaways') || '[]');
	} catch {
		items = [];
	}

	if (!Array.isArray(items) || !items.length) {
		list.innerHTML = '';
		empty.hidden = false;
		return;
	}

	empty.hidden = true;
	list.innerHTML = items.slice(0, 5).map((item) => (
		`<article class="rounded-xl border border-slate-200 bg-white p-3"><p class="text-sm text-slate-700">${escapeHtml(item.text || '')}</p><a href="${escapeHtml(item.url || '#')}" class="mt-2 inline-flex text-xs font-semibold text-cyan-700 hover:text-cyan-800">From: ${escapeHtml(item.title || 'Post')}</a></article>`
	)).join('');
}

function countThisWeek(dates) {
	if (!Array.isArray(dates)) return 0;
	const now = new Date();
	const day = now.getDay();
	const mondayOffset = day === 0 ? -6 : 1 - day;
	const monday = new Date(now);
	monday.setDate(now.getDate() + mondayOffset);
	monday.setHours(0, 0, 0, 0);

	return dates.reduce((count, value) => {
		const date = new Date(`${value}T00:00:00`);
		if (Number.isNaN(date.getTime())) return count;
		return date >= monday ? count + 1 : count;
	}, 0);
}

function computeStreak(dates) {
	if (!Array.isArray(dates) || !dates.length) return 0;

	const set = new Set(dates);
	let streak = 0;
	const cursor = new Date();

	while (true) {
		const key = cursor.toISOString().slice(0, 10);
		if (set.has(key)) {
			streak += 1;
			cursor.setDate(cursor.getDate() - 1);
			continue;
		}

		if (streak === 0) {
			cursor.setDate(cursor.getDate() - 1);
			const yesterday = cursor.toISOString().slice(0, 10);
			if (set.has(yesterday)) {
				streak = 1;
				cursor.setDate(cursor.getDate() - 1);
				continue;
			}
		}

		break;
	}

	return streak;
}

function escapeHtml(value) {
	return String(value)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;');
}

function initializeVoiceReader() {
	const content = document.getElementById('postContent');
	const toggle = document.getElementById('voiceReadToggle');
	const stop = document.getElementById('voiceReadStop');
	const status = document.getElementById('voiceReadStatus');

	if (!content || !toggle || !stop || !status) {
		return;
	}

	if (toggle.dataset.voiceBound === 'true') {
		return;
	}

	toggle.dataset.voiceBound = 'true';

	if (!('speechSynthesis' in window)) {
		toggle.disabled = true;
		stop.disabled = true;
		status.textContent = 'Voice reader is not supported in this browser.';
		return;
	}

	const synth = window.speechSynthesis;
	let active = false;
	let paused = false;
	let preferredVoice = pickPreferredVoice(synth, 'en-US');

	const refreshPreferredVoice = () => {
		preferredVoice = pickPreferredVoice(synth, 'en-US');
	};

	refreshPreferredVoice();
	synth.addEventListener('voiceschanged', refreshPreferredVoice);

	const setStatus = (message) => {
		status.textContent = message;
	};

	const setControls = () => {
		if (!active) {
			toggle.textContent = 'Listen';
			stop.disabled = true;
			return;
		}

		toggle.textContent = paused ? 'Resume' : 'Pause';
		stop.disabled = false;
	};

	const splitText = (text, max = 220) => {
		const chunks = [];
		let remaining = text.replace(/\s+/g, ' ').trim();

		while (remaining.length > max) {
			let cut = remaining.lastIndexOf('. ', max);
			if (cut < max * 0.5) {
				cut = remaining.lastIndexOf(' ', max);
			}
			if (cut <= 0) {
				cut = max;
			}

			chunks.push(remaining.slice(0, cut + 1).trim());
			remaining = remaining.slice(cut + 1).trim();
		}

		if (remaining) {
			chunks.push(remaining);
		}

		return chunks.filter(Boolean);
	};

	const stopReading = (message = 'Voice reader stopped.') => {
		synth.cancel();
		active = false;
		paused = false;
		setControls();
		setStatus(message);
	};

	const startReading = () => {
		const text = (content.textContent || '').trim();
		if (!text) {
			setStatus('No readable text found in this post.');
			return;
		}

		synth.cancel();

		const chunks = splitText(text);
		if (!chunks.length) {
			setStatus('No readable text found in this post.');
			return;
		}

		active = true;
		paused = false;
		setControls();
		setStatus('Reading aloud...');

		let remaining = chunks.length;

		chunks.forEach((chunk) => {
			const utterance = createHumanLikeUtterance(chunk, synth, 'en-US');
			if (preferredVoice) {
				utterance.voice = preferredVoice;
				utterance.lang = preferredVoice.lang || utterance.lang;
			}

			utterance.onend = () => {
				remaining -= 1;
				if (remaining <= 0 && active && !paused) {
					active = false;
					setControls();
					setStatus('Finished reading this post.');
				}
			};

			utterance.onerror = () => {
				stopReading('Voice reader encountered an error.');
			};

			synth.speak(utterance);
		});
	};

	toggle.addEventListener('click', () => {
		if (!active) {
			startReading();
			return;
		}

		if (paused) {
			synth.resume();
			paused = false;
			setControls();
			setStatus('Reading resumed.');
		} else {
			synth.pause();
			paused = true;
			setControls();
			setStatus('Reading paused.');
		}
	});

	stop.addEventListener('click', () => {
		stopReading();
	});

	window.addEventListener('beforeunload', () => {
		synth.removeEventListener('voiceschanged', refreshPreferredVoice);
		synth.cancel();
	});
}

function initializePostFeedbackPoll() {
	const panel = document.getElementById('postFeedbackPanel');
	const status = document.getElementById('postFeedbackStatus');
	if (!panel || !status) return;

	const buttons = [...panel.querySelectorAll('[data-feedback]')];
	if (!buttons.length) return;

	const key = panel.getAttribute('data-feedback-key') || '';
	if (!key) return;

	const render = (value) => {
		buttons.forEach((button) => {
			button.classList.toggle('bb-feedback-selected', button.getAttribute('data-feedback') === value);
		});

		if (value === 'yes') {
			status.textContent = 'Thanks. Marked as helpful.';
		} else if (value === 'no') {
			status.textContent = 'Thanks. Marked as not helpful.';
		} else {
			status.textContent = 'No feedback submitted yet.';
		}
	};

	render(localStorage.getItem(key));

	buttons.forEach((button) => {
		button.addEventListener('click', () => {
			const value = button.getAttribute('data-feedback');
			if (!value) return;

			localStorage.setItem(key, value);
			render(value);
			showToast(value === 'yes' ? 'Feedback saved: helpful.' : 'Feedback saved: not helpful.');
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
