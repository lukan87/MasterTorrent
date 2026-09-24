<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="html" encoding="UTF-8" indent="yes"/>

<xsl:template match="/rss/channel">

    <html lang="en">

        <head>

            <meta charset="UTF-8"/>

            <meta name="viewport"
                  content="width=device-width, initial-scale=1.0"/>

            <title>FileIplay RSS Feed</title>

            <style>

                * {
                    box-sizing: border-box;
                }

                html {
                    scroll-behavior: smooth;
                }

                body {
                    margin: 0;
                    padding: 0;

                    background:
                        radial-gradient(
                            circle at top,
                            #1c2230 0,
                            #0f1115 45%,
                            #0b0d11 100%
                        );

                    color: #e9ecef;

                    font-family:
                        Arial,
                        Helvetica,
                        sans-serif;

                    min-height: 100vh;
                }


                /* =========================================
                   HEADER
                   ========================================= */

                .header {
                    position: relative;

                    padding: 55px 20px 45px;

                    text-align: center;

                    background:
                        linear-gradient(
                            135deg,
                            #171a21,
                            #252b36
                        );

                    border-bottom: 1px solid #343a40;

                    overflow: hidden;
                }

                .header::before {
                    content: "";

                    position: absolute;

                    width: 300px;
                    height: 300px;

                    top: -180px;
                    left: 50%;

                    transform: translateX(-50%);

                    background: #0d6efd;

                    opacity: .08;

                    border-radius: 50%;

                    filter: blur(40px);
                }

                .logo {
                    position: relative;

                    font-size: 42px;

                    font-weight: 800;

                    letter-spacing: -1px;

                    color: #ffffff;
                }

                .logo span {
                    color: #0d6efd;
                }

                .subtitle {
                    position: relative;

                    margin-top: 9px;

                    color: #adb5bd;

                    font-size: 15px;
                }

                .rss-badge {
                    position: relative;

                    display: inline-flex;

                    align-items: center;
                    justify-content: center;

                    margin-top: 20px;

                    padding: 7px 15px;

                    border-radius: 30px;

                    background: #ff9800;

                    color: #111;

                    font-size: 11px;

                    font-weight: 800;

                    letter-spacing: .7px;

                    text-transform: uppercase;

                    box-shadow:
                        0 5px 20px rgba(255,152,0,.18);
                }


                /* =========================================
                   MAIN CONTAINER
                   ========================================= */

                .container {
                    width: 100%;

                    max-width: 1150px;

                    margin: 0 auto;

                    padding: 32px 20px 60px;
                }


                /* =========================================
                   FEED INFORMATION
                   ========================================= */

                .feed-info {
                    display: flex;

                    justify-content: space-between;
                    align-items: center;

                    gap: 20px;

                    margin-bottom: 24px;

                    padding: 20px 22px;

                    background:
                        linear-gradient(
                            135deg,
                            rgba(255,255,255,.035),
                            rgba(255,255,255,.015)
                        );

                    border: 1px solid #292e38;

                    border-radius: 15px;

                    box-shadow:
                        0 8px 25px rgba(0,0,0,.12);
                }

                .feed-heading {
                    min-width: 0;
                }

                .feed-title {
                    color: #ffffff;

                    font-size: 19px;

                    font-weight: 700;
                }

                .feed-subtitle {
                    margin-top: 5px;

                    color: #7f8996;

                    font-size: 12px;
                }

                .count {
                    flex-shrink: 0;

                    padding: 8px 13px;

                    border-radius: 20px;

                    background: rgba(13,110,253,.10);

                    border: 1px solid rgba(13,110,253,.25);

                    color: #8ab4f8;

                    font-size: 12px;

                    font-weight: 600;

                    white-space: nowrap;
                }

                .count-number {
                    color: #ffffff;

                    font-size: 14px;

                    font-weight: 800;
                }


                /* =========================================
                   TORRENT LIST
                   ========================================= */

                .items {
                    display: flex;

                    flex-direction: column;

                    gap: 20px;
                }


                /* =========================================
                   TORRENT CARD
                   ========================================= */

                .item {
                    position: relative;

                    overflow: hidden;

                    background:
                        linear-gradient(
                            135deg,
                            rgba(255,255,255,.025),
                            rgba(255,255,255,0)
                        ),
                        #181b22;

                    border: 1px solid #292e38;

                    border-radius: 16px;

                    padding: 20px;

                    transition:
                        transform .2s ease,
                        border-color .2s ease,
                        background .2s ease,
                        box-shadow .2s ease;
                }

                .item::before {
                    content: "";

                    position: absolute;

                    top: 0;
                    left: 0;

                    width: 3px;
                    height: 100%;

                    background: #0d6efd;

                    opacity: 0;

                    transition: opacity .2s ease;
                }

                .item:hover {
                    transform: translateY(-3px);

                    background:
                        linear-gradient(
                            135deg,
                            rgba(13,110,253,.055),
                            rgba(255,255,255,0)
                        ),
                        #1d2129;

                    border-color: #495464;

                    box-shadow:
                        0 14px 35px rgba(0,0,0,.30);
                }

                .item:hover::before {
                    opacity: 1;
                }


                /* =========================================
                   POSTER + INFORMATION
                   ========================================= */

                .torrent-content {
                    display: flex;

                    align-items: flex-start;

                    gap: 24px;
                }


                /* Poster */

                .poster {
                    flex: 0 0 160px;

                    width: 160px;
                }

                .poster img {
                    display: block;

                    width: 160px;
                    height: 240px;

                    object-fit: cover;

                    border-radius: 12px;

                    border: 1px solid #343a40;

                    background: #101217;

                    box-shadow:
                        0 10px 30px rgba(0,0,0,.45);

                    transition:
                        transform .25s ease,
                        box-shadow .25s ease;
                }

                .item:hover .poster img {
                    transform: scale(1.025);

                    box-shadow:
                        0 15px 35px rgba(0,0,0,.55);
                }


                /* Information */

                .torrent-info {
                    flex: 1;

                    min-width: 0;

                    display: flex;

                    flex-direction: column;

                    min-height: 240px;
                }


                /* =========================================
                   TITLE + CATEGORY
                   ========================================= */

                .item-header {
                    display: flex;

                    justify-content: space-between;
                    align-items: flex-start;

                    gap: 18px;
                }

                .title-wrapper {
                    flex: 1;

                    min-width: 0;
                }

                .title {
                    margin: 0;

                    font-size: 20px;

                    line-height: 1.45;

                    font-weight: 700;

                    overflow-wrap: anywhere;
                }

                .title a {
                    color: #ffffff;

                    text-decoration: none;

                    transition: color .2s ease;
                }

                .title a:hover {
                    color: #6ea8fe;
                }


                /* Category */

                .category {
                    flex-shrink: 0;

                    padding: 6px 12px;

                    border-radius: 20px;

                    background:
                        rgba(13,110,253,.10);

                    border: 1px solid
                        rgba(13,110,253,.25);

                    color: #8ab4f8;

                    font-size: 12px;

                    font-weight: 600;

                    white-space: nowrap;
                }


                /* =========================================
                   META INFORMATION
                   ========================================= */

                .meta {
                    display: flex;

                    flex-wrap: wrap;

                    gap: 10px;

                    margin-top: auto;

                    padding-top: 22px;
                }

                .meta-item {
                    display: inline-flex;

                    align-items: center;

                    gap: 7px;

                    padding: 7px 11px;

                    border-radius: 8px;

                    background: #212631;

                    border: 1px solid #303746;

                    color: #8f98a3;

                    font-size: 12px;
                }

                .meta-icon {
                    font-size: 13px;
                }


                /* =========================================
                   BUTTONS
                   ========================================= */

                .buttons {
                    display: flex;

                    flex-wrap: wrap;

                    gap: 10px;

                    margin-top: 18px;

                    padding-top: 18px;

                    border-top: 1px solid #292e38;
                }

                .button {
                    display: inline-flex;

                    align-items: center;
                    justify-content: center;

                    gap: 7px;

                    padding: 10px 16px;

                    border-radius: 9px;

                    font-size: 13px;

                    font-weight: 600;

                    text-decoration: none;

                    transition:
                        transform .2s ease,
                        background .2s ease,
                        box-shadow .2s ease;
                }

                .button:hover {
                    transform: translateY(-1px);
                }

                .button-icon {
                    font-size: 14px;
                }


                /* Download */

                .download {
                    background: #0d6efd;

                    color: #ffffff;

                    box-shadow:
                        0 5px 15px
                        rgba(13,110,253,.18);
                }

                .download:hover {
                    background: #0b5ed7;

                    color: #ffffff;

                    box-shadow:
                        0 7px 20px
                        rgba(13,110,253,.28);
                }


                /* View Torrent */

                .details {
                    background: #292e38;

                    color: #ffffff;

                    border: 1px solid #3a414e;
                }

                .details:hover {
                    background: #343a46;

                    color: #ffffff;
                }


                /* =========================================
                   FOOTER
                   ========================================= */

                .footer {
                    margin-top: 45px;

                    padding: 30px 20px;

                    text-align: center;

                    border-top: 1px solid #292e38;

                    color: #6c757d;
                }

                .footer-title {
                    color: #adb5bd;

                    font-size: 14px;

                    font-weight: 600;
                }

                .footer-text {
                    margin-top: 6px;

                    color: #6c757d;

                    font-size: 12px;
                }

                .footer-link {
                    display: inline-flex;

                    align-items: center;

                    margin-top: 14px;

                    padding: 8px 14px;

                    border-radius: 8px;

                    background: #212631;

                    border: 1px solid #303746;

                    color: #6ea8fe;

                    font-size: 12px;

                    font-weight: 600;

                    text-decoration: none;

                    transition:
                        background .2s ease,
                        border-color .2s ease;
                }

                .footer-link:hover {
                    background: #292e38;

                    border-color: #495464;

                    color: #8ab4f8;
                }


                /* =========================================
                   TABLET
                   ========================================= */

                @media (max-width: 800px) {

                    .container {
                        padding:
                            25px 15px 50px;
                    }

                    .torrent-content {
                        gap: 18px;
                    }

                    .poster {
                        flex: 0 0 135px;

                        width: 135px;
                    }

                    .poster img {
                        width: 135px;

                        height: 203px;
                    }

                    .torrent-info {
                        min-height: 203px;
                    }

                    .title {
                        font-size: 18px;
                    }

                }


                /* =========================================
                   MOBILE
                   ========================================= */

                @media (max-width: 650px) {

                    .header {
                        padding:
                            38px 15px 32px;
                    }

                    .logo {
                        font-size: 32px;
                    }

                    .subtitle {
                        font-size: 13px;
                    }

                    .container {
                        padding:
                            18px 12px 40px;
                    }

                    .feed-info {
                        flex-direction: column;

                        align-items: flex-start;

                        gap: 12px;

                        padding: 17px;
                    }

                    .count {
                        align-self: flex-start;
                    }

                    .item {
                        padding: 15px;

                        border-radius: 13px;
                    }

                    .torrent-content {
                        gap: 15px;
                    }

                    .poster {
                        flex: 0 0 105px;

                        width: 105px;
                    }

                    .poster img {
                        width: 105px;

                        height: 158px;

                        border-radius: 9px;
                    }

                    .torrent-info {
                        min-height: 158px;
                    }

                    .item-header {
                        flex-direction: column;

                        gap: 9px;
                    }

                    .title {
                        font-size: 16px;

                        line-height: 1.4;
                    }

                    .category {
                        align-self: flex-start;
                    }

                    .meta {
                        padding-top: 15px;

                        gap: 7px;
                    }

                    .meta-item {
                        padding: 6px 9px;

                        font-size: 11px;
                    }

                    .buttons {
                        margin-top: 13px;

                        padding-top: 13px;
                    }

                    .button {
                        padding: 9px 12px;

                        font-size: 12px;
                    }

                }


                /* =========================================
                   SMALL PHONES
                   ========================================= */

                @media (max-width: 480px) {

                    .torrent-content {
                        flex-direction: column;
                    }

                    .poster {
                        flex: none;

                        width: 125px;
                    }

                    .poster img {
                        width: 125px;

                        height: 188px;
                    }

                    .torrent-info {
                        width: 100%;

                        min-height: auto;
                    }

                    .title {
                        font-size: 17px;
                    }

                    .meta {
                        flex-direction: column;

                        align-items: flex-start;
                    }

                    .meta-item {
                        width: 100%;
                    }

                    .buttons {
                        flex-direction: column;
                    }

                    .button {
                        width: 100%;
                    }

                }


                /* =========================================
                   VERY SMALL PHONES
                   ========================================= */

                @media (max-width: 350px) {

                    .logo {
                        font-size: 28px;
                    }

                    .poster {
                        width: 110px;
                    }

                    .poster img {
                        width: 110px;

                        height: 165px;
                    }

                    .title {
                        font-size: 16px;
                    }

                }

            </style>

        </head>


        <body>

            <!-- =========================================
                 HEADER
                 ========================================= -->

            <div class="header">

                <div class="logo">
                    File<span>I</span>play
                </div>

                <div class="subtitle">
                    Latest torrents and releases
                </div>

                <div class="rss-badge">
                    RSS Feed
                </div>

            </div>


            <!-- =========================================
                 MAIN
                 ========================================= -->

            <div class="container">


                <!-- Feed information -->

                <div class="feed-info">

                    <div class="feed-heading">

                        <div class="feed-title">
                            <xsl:value-of select="title"/>
                        </div>

                        <div class="feed-subtitle">
                            Latest releases from FileIplay
                        </div>

                    </div>

                    <div class="count">

                        <span class="count-number">
                            <xsl:value-of select="count(item)"/>
                        </span>

                        releases

                    </div>

                </div>


                <!-- Torrent list -->

                <div class="items">

                    <xsl:for-each select="item">

                        <article class="item">

                            <div class="torrent-content">


                                <!-- =================================
                                     POSTER
                                     ================================= -->

                                <xsl:if test="rssImage">

                                    <div class="poster">

                                        <img>

                                            <xsl:attribute name="src">
                                                <xsl:value-of
                                                    select="normalize-space(rssImage)"/>
                                            </xsl:attribute>

                                            <xsl:attribute name="alt">
                                                <xsl:value-of
                                                    select="title"/>
                                            </xsl:attribute>

                                        </img>

                                    </div>

                                </xsl:if>


                                <!-- =================================
                                     TORRENT INFORMATION
                                     ================================= -->

                                <div class="torrent-info">


                                    <!-- Title + category -->

                                    <div class="item-header">

                                        <div class="title-wrapper">

                                            <h2 class="title">

                                                <a href="{guid}">

                                                    <xsl:value-of
                                                        select="title"/>

                                                </a>

                                            </h2>

                                        </div>


                                        <div class="category">

                                            <xsl:value-of
                                                select="category"/>

                                        </div>

                                    </div>


                                    <!-- Meta -->

                                    <div class="meta">

                                        <span class="meta-item">

                                            <span class="meta-icon">
                                                📅
                                            </span>

                                            <span>
                                                <xsl:value-of
                                                    select="pubDate"/>
                                            </span>

                                        </span>


                                        <span class="meta-item">

                                            <span class="meta-icon">
                                                📂
                                            </span>

                                            <span>
                                                <xsl:value-of
                                                    select="category"/>
                                            </span>

                                        </span>

                                    </div>


                                    <!-- Buttons -->

                                    <div class="buttons">

                                        <a class="button download"
                                           href="{link}">

                                            <span class="button-icon">
                                                ⬇
                                            </span>

                                            Download Torrent

                                        </a>


                                        <a class="button details"
                                           href="{guid}">

                                            <span class="button-icon">
                                                ↗
                                            </span>

                                            View Torrent

                                        </a>

                                    </div>

                                </div>

                            </div>

                        </article>

                    </xsl:for-each>

                </div>


                <!-- =========================================
                     FOOTER
                     ========================================= -->

                <div class="footer">

                    <div class="footer-title">
                        FileIplay RSS Feed
                    </div>

                    <div class="footer-text">
                        Latest torrents and releases from FileIplay
                    </div>

                    <a class="footer-link" href="{link}">
                        Visit FileIplay
                    </a>

                </div>

            </div>

        </body>

    </html>

</xsl:template>

</xsl:stylesheet>