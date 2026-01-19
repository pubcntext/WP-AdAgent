/**
 * WP-AdAgent Context Extractor
 *
 * Extracts semantic context from the page for use in
 * Pubcontext API matching.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

(function() {
    'use strict';

    /**
     * Context Extractor class
     */
    class ContextExtractor {
        constructor() {
            this.startTime = Date.now();
            this.maxScrollDepth = 0;
            this.mouseDetected = false;

            // Track user engagement
            this.initEngagementTracking();
        }

        /**
         * Initialize engagement tracking
         */
        initEngagementTracking() {
            // Track scroll depth
            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrollPercent = scrollHeight > 0 ? Math.round((scrollTop / scrollHeight) * 100) : 0;

                if (scrollPercent > this.maxScrollDepth) {
                    this.maxScrollDepth = scrollPercent;
                }
            }, { passive: true });

            // Track mouse movement
            window.addEventListener('mousemove', () => {
                this.mouseDetected = true;
            }, { once: true, passive: true });
        }

        /**
         * Extract all page context
         */
        extractPageContext() {
            return {
                ...this.extractPageContent(),
                ...this.extractUserSignals(),
                extractedAt: new Date().toISOString()
            };
        }

        /**
         * Extract page content context
         */
        extractPageContent() {
            return {
                // Basic page info
                title: this.getPageTitle(),
                description: this.getMetaDescription(),
                url: window.location.href,
                referrer: document.referrer,

                // Content extraction
                headings: this.getHeadings(),
                bodyText: this.getBodyText(500),
                keywords: this.getKeywords(),

                // Structured data
                publishDate: this.getPublishDate(),
                author: this.getAuthor(),
                categories: this.getCategories(),
                tags: this.getTags(),

                // Page type hints
                pageType: this.getPageType(),
                language: this.getLanguage()
            };
        }

        /**
         * Extract user engagement signals
         */
        extractUserSignals() {
            return {
                device: this.getDeviceType(),
                timeOnPage: Date.now() - this.startTime,
                scrollDepth: this.maxScrollDepth,
                mouseDetected: this.mouseDetected,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };
        }

        /**
         * Get page title
         */
        getPageTitle() {
            // Try Open Graph first
            const ogTitle = document.querySelector('meta[property="og:title"]');
            if (ogTitle) return ogTitle.content;

            // Fall back to document title
            return document.title || '';
        }

        /**
         * Get meta description
         */
        getMetaDescription() {
            // Try Open Graph
            const ogDesc = document.querySelector('meta[property="og:description"]');
            if (ogDesc) return ogDesc.content;

            // Try standard meta description
            const metaDesc = document.querySelector('meta[name="description"]');
            if (metaDesc) return metaDesc.content;

            return '';
        }

        /**
         * Get headings (H1-H3)
         */
        getHeadings() {
            const headings = [];

            document.querySelectorAll('h1, h2, h3').forEach(heading => {
                const text = heading.textContent.trim();
                if (text && text.length > 0) {
                    headings.push({
                        level: parseInt(heading.tagName.charAt(1)),
                        text: text.substring(0, 200)
                    });
                }
            });

            return headings.slice(0, 10); // Limit to first 10
        }

        /**
         * Get body text excerpt
         */
        getBodyText(maxChars) {
            // Try to find main content area
            const contentSelectors = [
                'article',
                '[role="main"]',
                '.entry-content',
                '.post-content',
                '.content',
                'main',
                '#content'
            ];

            let contentElement = null;
            for (const selector of contentSelectors) {
                contentElement = document.querySelector(selector);
                if (contentElement) break;
            }

            if (!contentElement) {
                contentElement = document.body;
            }

            // Get text content, excluding scripts and styles
            const clone = contentElement.cloneNode(true);
            clone.querySelectorAll('script, style, nav, header, footer, aside').forEach(el => el.remove());

            let text = clone.textContent || '';
            text = text.replace(/\s+/g, ' ').trim();

            return text.substring(0, maxChars);
        }

        /**
         * Get keywords from meta tags and schema
         */
        getKeywords() {
            const keywords = new Set();

            // Meta keywords
            const metaKeywords = document.querySelector('meta[name="keywords"]');
            if (metaKeywords && metaKeywords.content) {
                metaKeywords.content.split(',').forEach(kw => {
                    const trimmed = kw.trim().toLowerCase();
                    if (trimmed) keywords.add(trimmed);
                });
            }

            // Article tags from schema.org
            const articleTags = document.querySelectorAll('[itemprop="keywords"]');
            articleTags.forEach(tag => {
                const text = tag.textContent || tag.content;
                if (text) {
                    text.split(',').forEach(kw => {
                        const trimmed = kw.trim().toLowerCase();
                        if (trimmed) keywords.add(trimmed);
                    });
                }
            });

            // News keywords
            const newsKeywords = document.querySelector('meta[name="news_keywords"]');
            if (newsKeywords && newsKeywords.content) {
                newsKeywords.content.split(',').forEach(kw => {
                    const trimmed = kw.trim().toLowerCase();
                    if (trimmed) keywords.add(trimmed);
                });
            }

            return Array.from(keywords).slice(0, 20);
        }

        /**
         * Get publish date
         */
        getPublishDate() {
            // Article published time (Open Graph)
            const ogDate = document.querySelector('meta[property="article:published_time"]');
            if (ogDate) return ogDate.content;

            // Schema.org datePublished
            const schemaDate = document.querySelector('[itemprop="datePublished"]');
            if (schemaDate) {
                return schemaDate.content || schemaDate.getAttribute('datetime') || schemaDate.textContent;
            }

            // Time element with datetime
            const timeElement = document.querySelector('time[datetime]');
            if (timeElement) return timeElement.getAttribute('datetime');

            return null;
        }

        /**
         * Get author
         */
        getAuthor() {
            // Open Graph
            const ogAuthor = document.querySelector('meta[property="article:author"]');
            if (ogAuthor) return ogAuthor.content;

            // Schema.org
            const schemaAuthor = document.querySelector('[itemprop="author"]');
            if (schemaAuthor) {
                const name = schemaAuthor.querySelector('[itemprop="name"]');
                return name ? name.textContent : schemaAuthor.textContent;
            }

            // Meta author
            const metaAuthor = document.querySelector('meta[name="author"]');
            if (metaAuthor) return metaAuthor.content;

            return null;
        }

        /**
         * Get categories (from WordPress or schema)
         */
        getCategories() {
            // This is often passed via wpAdAgentConfig
            if (typeof wpAdAgentConfig !== 'undefined' && wpAdAgentConfig.pageData) {
                return wpAdAgentConfig.pageData.categories || [];
            }

            // Try to extract from page
            const categories = [];
            document.querySelectorAll('.category, [rel="category"], [itemprop="articleSection"]').forEach(el => {
                const text = el.textContent.trim();
                if (text && !categories.includes(text)) {
                    categories.push(text);
                }
            });

            return categories.slice(0, 10);
        }

        /**
         * Get tags
         */
        getTags() {
            // This is often passed via wpAdAgentConfig
            if (typeof wpAdAgentConfig !== 'undefined' && wpAdAgentConfig.pageData) {
                return wpAdAgentConfig.pageData.tags || [];
            }

            // Try to extract from page
            const tags = [];
            document.querySelectorAll('.tag, [rel="tag"]').forEach(el => {
                const text = el.textContent.trim();
                if (text && !tags.includes(text)) {
                    tags.push(text);
                }
            });

            return tags.slice(0, 20);
        }

        /**
         * Get page type
         */
        getPageType() {
            // Check Open Graph type
            const ogType = document.querySelector('meta[property="og:type"]');
            if (ogType) return ogType.content;

            // Check body classes (WordPress)
            const body = document.body;
            if (body.classList.contains('single-post') || body.classList.contains('single')) {
                return 'article';
            }
            if (body.classList.contains('home') || body.classList.contains('blog')) {
                return 'website';
            }
            if (body.classList.contains('page')) {
                return 'page';
            }
            if (body.classList.contains('archive') || body.classList.contains('category')) {
                return 'archive';
            }

            return 'website';
        }

        /**
         * Get page language
         */
        getLanguage() {
            return document.documentElement.lang ||
                   document.querySelector('meta[http-equiv="content-language"]')?.content ||
                   'en';
        }

        /**
         * Get device type
         */
        getDeviceType() {
            const width = window.innerWidth;

            if (width < 768) return 'mobile';
            if (width < 1024) return 'tablet';
            return 'desktop';
        }
    }

    // Export to global scope
    window.wpAdAgentContext = new ContextExtractor();

})();
