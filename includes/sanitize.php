<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Basic HTML sanitizer: remove script/style tags and strip event handler attributes and javascript: URLs.
function sanitize_html($html) {
    // Remove script and style blocks
    $html = preg_replace('#<script[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('#<style[^>]*>.*?</style>#is', '', $html);

    // Load into DOMDocument
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    // Ensure proper encoding
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Remove attributes that start with 'on' (event handlers) and javascript: href/src
    $xpath = new DOMXPath($doc);
    foreach ($xpath->query('//@*') as $attr) {
        // ensure we have a DOMAttr and an owner element
        if (!($attr instanceof DOMAttr)) continue;
        $el = $attr->ownerElement;
        if (!$el) continue;

        $name = strtolower($attr->name);
        $value = $attr->value;

        // remove event handler attributes like onclick, onerror
        if (strpos($name, 'on') === 0) {
            $el->removeAttribute($attr->name);
            continue;
        }

        // Remove javascript: in href/src/style values
        if (is_string($value) && preg_match('/^\s*javascript:/i', $value)) {
            $el->removeAttribute($attr->name);
            continue;
        }
    }

    // Allow only safe tags: p, br, b, strong, i, em, u, a, ul, ol, li, img, h1,h2,h3,blockquote,code,pre
    $allowed = ['p','br','b','strong','i','em','u','a','ul','ol','li','img','h1','h2','h3','blockquote','code','pre','table','thead','tbody','tr','td','th'];
    $nodes = $doc->getElementsByTagName('*');
    // traverse in reverse to safely remove
    for ($i = $nodes->length -1; $i >= 0; $i--) {
        $node = $nodes->item($i);
        $tag = strtolower($node->nodeName);
        if (!in_array($tag, $allowed, true)) {
            // replace node with its children (if parent exists)
            $parent = $node->parentNode;
            if ($parent) {
                $frag = $doc->createDocumentFragment();
                while ($node->childNodes->length > 0) {
                    $frag->appendChild($node->childNodes->item(0));
                }
                $parent->replaceChild($frag, $node);
            }
        }
    }

    // Return inner HTML of body for a clean result (avoid DOCTYPE/html wrappers)
    $bodyNode = $doc->getElementsByTagName('body')->item(0);
    if ($bodyNode) {
        $inner = '';
        foreach ($bodyNode->childNodes as $child) {
            $inner .= $doc->saveHTML($child);
        }
        return trim($inner);
    }

    $body = $doc->saveHTML();
    // fallback: strip XML/doctype wrappers
    $body = preg_replace('/^<\?xml.+?\?>/s', '', $body);
    $body = preg_replace('/^<!DOCTYPE.+?>/is', '', $body);
    $body = str_replace(['<html>','</html>','<body>','</body>'], '', $body);
    return trim($body);
}
?>