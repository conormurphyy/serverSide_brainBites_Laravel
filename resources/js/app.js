import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
	initializeThemeToggle();
	initializeTopicMap();
	initializeHeroWaveCanvases();
	initializeTiltCards();
	initializePostPreview();
	initializeReadingTools();
	initializeBrainBot();

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
	const toggle = document.getElementById('themeToggle');
	const key = 'bb-theme';

	const applyTheme = (theme) => {
		const dark = theme === 'dark';
		root.classList.toggle('theme-dark', dark);
		if (toggle) {
			toggle.textContent = dark ? 'Light mode' : 'Dark mode';
		}
	};

	const saved = localStorage.getItem(key);
	if (saved === 'dark' || saved === 'light') {
		applyTheme(saved);
	} else {
		const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
		applyTheme(prefersDark ? 'dark' : 'light');
	}

	if (!toggle) {
		return;
	}

	toggle.addEventListener('click', () => {
		const next = root.classList.contains('theme-dark') ? 'light' : 'dark';
		localStorage.setItem(key, next);
		applyTheme(next);
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
	buttons.forEach((button) => {
		button.addEventListener('click', () => {
			const size = button.dataset.fontSize;
			content.classList.remove('bb-reading-small', 'bb-reading-large');

			if (size === 'small') {
				content.classList.add('bb-reading-small');
			} else if (size === 'large') {
				content.classList.add('bb-reading-large');
			}
		});
	});
}

function initializeHeroWaveCanvases() {
	const canvases = document.querySelectorAll('[data-hero-wave]');

	if (!canvases.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	canvases.forEach((canvas) => {
		const ctx = canvas.getContext('2d');
		if (!ctx) {
			return;
		}

		const resize = () => {
			const width = canvas.clientWidth;
			const height = canvas.clientHeight;
			canvas.width = Math.max(1, Math.floor(width * window.devicePixelRatio));
			canvas.height = Math.max(1, Math.floor(height * window.devicePixelRatio));
			ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
		};

		resize();
		window.addEventListener('resize', resize);

		let frame = 0;

		const draw = () => {
			const width = canvas.clientWidth;
			const height = canvas.clientHeight;
			ctx.clearRect(0, 0, width, height);

			for (let layer = 0; layer < 3; layer += 1) {
				ctx.beginPath();
				const amplitude = 8 + (layer * 4);
				const speed = 0.01 + (layer * 0.0035);
				const offset = layer * 1.7;

				for (let x = 0; x <= width; x += 4) {
					const y = (height * 0.5)
						+ Math.sin((x * 0.03) + (frame * speed) + offset) * amplitude
						+ Math.cos((x * 0.01) + (frame * speed * 1.7)) * (amplitude * 0.35);

					if (x === 0) {
						ctx.moveTo(x, y);
					} else {
						ctx.lineTo(x, y);
					}
				}

				ctx.strokeStyle = `rgba(${layer === 0 ? '34,211,238' : layer === 1 ? '163,230,53' : '255,255,255'}, ${0.3 - (layer * 0.07)})`;
				ctx.lineWidth = 1.3;
				ctx.stroke();
			}

			frame += 1;
			window.requestAnimationFrame(draw);
		};

		draw();
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
