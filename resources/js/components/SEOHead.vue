<template>
    <!-- Компонент не рендерит ничего визуально, только управляет метатегами -->
</template>

<script>
import { onMounted, watchEffect, onBeforeUnmount } from 'vue';

export default {
    name: 'SEOHead',
    props: {
        title: {
            type: String,
            default: '',
        },
        description: {
            type: String,
            default: '',
        },
        keywords: {
            type: String,
            default: '',
        },
        ogImage: {
            type: String,
            default: '',
        },
        ogType: {
            type: String,
            default: 'website',
        },
        canonical: {
            type: String,
            default: '',
        },
        noindex: {
            type: Boolean,
            default: false,
        },
        schema: {
            type: [Object, Array],
            default: null,
        },
    },
    setup(props) {
        const createdElements = [];
        let lastTitle = '';
        let lastDescription = '';

        const setMetaTag = (attributes) => {
            const selector = attributes.name 
                ? `meta[name="${attributes.name}"]`
                : `meta[property="${attributes.property}"]`;
            
            let element = document.querySelector(selector);
            
            if (!element) {
                element = document.createElement('meta');
                Object.keys(attributes).forEach(key => {
                    element.setAttribute(key, attributes[key]);
                });
                document.head.appendChild(element);
                createdElements.push(element);
            } else {
                Object.keys(attributes).forEach(key => {
                    element.setAttribute(key, attributes[key]);
                });
            }
        };

        const setLinkTag = (attributes) => {
            const selector = `link[rel="${attributes.rel}"]`;
            let element = document.querySelector(selector);
            
            if (!element) {
                element = document.createElement('link');
                Object.keys(attributes).forEach(key => {
                    element.setAttribute(key, attributes[key]);
                });
                document.head.appendChild(element);
                createdElements.push(element);
            } else {
                Object.keys(attributes).forEach(key => {
                    element.setAttribute(key, attributes[key]);
                });
            }
        };

        const setSchemaScript = (schema) => {
            // Удаляем предыдущий schema script если есть
            const existingScript = document.querySelector('script[type="application/ld+json"]');
            if (existingScript) {
                existingScript.remove();
            }

            const script = document.createElement('script');
            script.type = 'application/ld+json';
            script.text = JSON.stringify(schema);
            document.head.appendChild(script);
            createdElements.push(script);
        };

        const updateMeta = () => {
            console.log('[SEO DEBUG] SEOHead.updateMeta вызван, текущий title:', document.title);
            console.log('[SEO DEBUG] props.title:', props.title);
            console.log('[SEO DEBUG] props.description:', props.description);
            console.log('[SEO DEBUG] lastTitle:', lastTitle);
            
            // Title - обновляем всегда, даже если пустой
            const titleToSet = props.title || 'Lagom - Профессиональные услуги по работе с земельными участками';
            
            // Description - обновляем всегда
            const descriptionToSet = props.description || 'Профессиональные услуги по подбору и оформлению земельных участков';
            
            console.log('[SEO DEBUG] titleToSet:', titleToSet);
            console.log('[SEO DEBUG] descriptionToSet:', descriptionToSet);
            
            // Проверяем, изменились ли значения, чтобы избежать лишних обновлений
            // Но всегда обновляем при первом вызове (когда lastTitle пустой)
            if (lastTitle && titleToSet === lastTitle && descriptionToSet === lastDescription) {
                console.log('[SEO DEBUG] Значения не изменились, пропускаем обновление');
                return; // Значения не изменились, не обновляем
            }
            
            // Сохраняем текущие значения
            lastTitle = titleToSet;
            lastDescription = descriptionToSet;
            
            console.log('[SEO DEBUG] Обновляем title с', document.title, 'на', titleToSet);
            // Обновляем title - всегда, чтобы предотвратить сброс
            document.title = titleToSet;
            console.log('[SEO DEBUG] Title обновлен на:', document.title);
            
            setMetaTag({ name: 'description', content: descriptionToSet });

            // Keywords
            if (props.keywords) {
                setMetaTag({ name: 'keywords', content: props.keywords });
            }

            // Robots
            if (props.noindex) {
                setMetaTag({ name: 'robots', content: 'noindex, nofollow' });
            } else {
                setMetaTag({ name: 'robots', content: 'index, follow' });
            }

            // Open Graph
            setMetaTag({ property: 'og:title', content: titleToSet });
            setMetaTag({ property: 'og:description', content: descriptionToSet });
            setMetaTag({ property: 'og:type', content: props.ogType });
            setMetaTag({ property: 'og:url', content: props.canonical || window.location.href });

            if (props.ogImage) {
                const imageUrl = props.ogImage.startsWith('http') 
                    ? props.ogImage 
                    : window.location.origin + props.ogImage;
                setMetaTag({ property: 'og:image', content: imageUrl });
            }

            // Twitter Cards
            setMetaTag({ name: 'twitter:card', content: 'summary_large_image' });
            setMetaTag({ name: 'twitter:title', content: titleToSet });
            setMetaTag({ name: 'twitter:description', content: descriptionToSet });

            if (props.ogImage) {
                const imageUrl = props.ogImage.startsWith('http') 
                    ? props.ogImage 
                    : window.location.origin + props.ogImage;
                setMetaTag({ name: 'twitter:image', content: imageUrl });
            }

            // Canonical
            if (props.canonical) {
                setLinkTag({ rel: 'canonical', href: props.canonical });
            }

            // Schema.org JSON-LD
            if (props.schema) {
                setSchemaScript(props.schema);
            }
        };

        // Обновляем метатеги сразу при создании компонента (до монтирования)
        // Это важно для предотвращения мерцания title
        console.log('[SEO DEBUG] SEOHead setup: вызов updateMeta при создании компонента');
        updateMeta();
        
        // Используем watchEffect для отслеживания всех изменений props
        // Отслеживаем все изменения props и обновляем метатеги
        watchEffect(() => {
            console.log('[SEO DEBUG] SEOHead watchEffect: props изменились');
            // Обновляем метатеги при любом изменении props
            // watchEffect автоматически отслеживает все используемые props
            const title = props.title;
            const description = props.description;
            const keywords = props.keywords;
            const canonical = props.canonical;
            const ogImage = props.ogImage;
            const schema = props.schema;
            
            console.log('[SEO DEBUG] SEOHead watchEffect: title =', title, 'description =', description);
            // Обновляем метатеги при изменении
            updateMeta();
        });

        onMounted(() => {
            console.log('[SEO DEBUG] SEOHead onMounted: компонент смонтирован');
            // Обновляем после монтирования для надежности
            updateMeta();
        });

        onBeforeUnmount(() => {
            // Очищаем созданные элементы при размонтировании
            createdElements.forEach(el => {
                if (el && el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            });
        });

        return {};
    },
};
</script>

