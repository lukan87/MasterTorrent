<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $siteUrl = rtrim((string) config('app.url'), '/');
        $footer = "\n\nSee you on FileIplay,\nThe FileIplay Team\n{$siteUrl}\n\nTo stop receiving community emails, sign in and turn off \"Email subscribe\" on your profile.";

        $templates = [
            [
                'name' => 'Welcome to FileIplay',
                'subject' => 'Welcome to the FileIplay community, {name}',
                'body' => <<<TEXT
Hi {name},

Welcome to FileIplay! We're glad you're here.

Get started at your own pace:
- Explore the site and find something that interests you.
- Visit the forum to say hello and meet other members.
- Read the community rules before getting involved.

Your contributions help make this a friendly place to share and discover together. If you need a hand, ask in the forum and our community can help.

Visit FileIplay: {$siteUrl}
Join the conversation: {$siteUrl}/forum
TEXT,
            ],
            [
                'name' => 'Community Update',
                'subject' => 'Your latest FileIplay community update',
                'body' => <<<TEXT
Hi {name},

Here's the latest from FileIplay.

WHAT'S NEW
[Describe the update and what it means for members.]

TAKE A LOOK
[Add a link to the announcement or feature.]

We'd love to hear what you think. Share your feedback in the forum and help shape what comes next.

Visit the forum: {$siteUrl}/forum

Thanks for being part of our community.
TEXT,
            ],
            [
                'name' => 'Weekly Roundup',
                'subject' => 'This week on FileIplay: your community roundup',
                'body' => <<<TEXT
Hi {name},

Take a moment to catch up with FileIplay this week.

THIS WEEK'S HIGHLIGHTS
- [First highlight and link]
- [Second highlight and link]
- [Third highlight and link]

FROM THE COMMUNITY
[Spotlight a helpful member, an interesting discussion, or a community milestone.]

LOOKING AHEAD
[Add an upcoming activity or something members can look forward to.]

Catch up and join in: {$siteUrl}
TEXT,
            ],
            [
                'name' => 'Welcome Back',
                'subject' => '{name}, come catch up with FileIplay',
                'body' => <<<TEXT
Hi {name},

It's been a while, and we'd love to see you around FileIplay again.

Drop in when you have a moment, explore the site, or join a conversation in the forum. There's no pressure to catch up on everything: start with what interests you.

Visit FileIplay: {$siteUrl}
Visit the forum: {$siteUrl}/forum

If you need help getting back into your account, use the password reset option on the sign-in page.

We hope to see you soon.
TEXT,
            ],
            [
                'name' => 'Scheduled Maintenance',
                'subject' => 'FileIplay maintenance notice: [date]',
                'body' => <<<TEXT
Hi {name},

We're planning some maintenance to keep FileIplay running smoothly.

WHEN
[Date, start time, end time, and time zone]

WHAT TO EXPECT
[Explain which services may be unavailable and for how long.]

WHAT YOU NEED TO DO
[Include any steps members should take, or say that no action is needed.]

We'll share updates on the site when available: {$siteUrl}

Thank you for your patience while we take care of this work.
TEXT,
            ],
            [
                'name' => 'Thank You to Our Members',
                'subject' => 'Thank you for being part of FileIplay, {name}',
                'body' => <<<TEXT
Hi {name},

A community is made by the people who show up for it. Thank you for being part of FileIplay.

Whether you're helping another member, joining a discussion, or sharing something useful, your participation makes a difference.

We appreciate the time and care our members put into this community. If you have an idea to make FileIplay better, we'd love to hear it in the forum.

Share your ideas: {$siteUrl}/forum

Thanks for helping make FileIplay a welcoming place.
TEXT,
            ],
        ];

        foreach ($templates as $template) {
            // Keep existing templates and any administrator edits on subsequent runs.
            EmailTemplate::firstOrCreate(
                ['name' => $template['name']],
                ['subject' => $template['subject'], 'body' => $template['body'].$footer]
            );
        }
    }
}
