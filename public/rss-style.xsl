<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">
        <html>
            <head>
                <title>MySite RSS Feed</title>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; }
                    h2 { color: #333; }
                    .rss-item { border-bottom: 1px solid #ccc; padding: 10px 0; }
                    .rss-item a { text-decoration: none; font-weight: bold; color: #007bff; }
                    .rss-item a:hover { text-decoration: underline; }
                    .rss-category { font-size: 12px; color: #666; }
                    .rss-description { margin-top: 5px; }
                </style>
            </head>
            <body>
                <h2>MySite RSS Feed</h2>
                <xsl:for-each select="rss/channel/item">
                    <div class="rss-item">
                        <a>
                            <xsl:attribute name="href"><xsl:value-of select="link"/></xsl:attribute>
                            <xsl:value-of select="title"/>
                        </a>
                        <div class="rss-category">Category: <xsl:value-of select="category"/></div>
                        <div class="rss-description"><xsl:value-of select="description"/></div>
                        <div class="rss-date"><small><xsl:value-of select="pubDate"/></small></div>
                    </div>
                </xsl:for-each>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
