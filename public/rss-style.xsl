<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes"/>
    <xsl:template match="/">
        <html>
            <head>
                <title>Styled RSS Feed</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    h1 { color: #0066cc; }
                    div.item { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; }
                    a { text-decoration: none; color: #0066cc; }
                    a:hover { text-decoration: underline; }
                </style>
            </head>
            <body>
                <h1>LastFiles RSS Feed</h1>
                <xsl:for-each select="rss/channel/item">
                    <div class="item">
                        <h2>
                            <a href="{link}"><xsl:value-of select="title"/></a>
                        </h2>
                        <p><xsl:value-of select="description"/></p>
                        <p><strong>Category:</strong> <xsl:value-of select="category"/></p>
                        <p><strong>Published:</strong> <xsl:value-of select="pubDate"/></p>
                    </div>
                </xsl:for-each>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
