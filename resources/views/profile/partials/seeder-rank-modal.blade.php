<div class="modal fade seeder-rank-modal profile-guide-modal" id="seederRankModal" tabindex="-1" aria-labelledby="seederRankModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="seeder-guide-eyebrow"><i class="bi bi-award" aria-hidden="true"></i> Keep the swarm alive</span>
                    <h2 class="modal-title" id="seederRankModalTitle">Seeder Rank System</h2>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="seeder-guide-current">
                    <span>{{ $user->seeder_icon }} {{ $user->seeder_rank_name }}</span>
                    <strong>{{ number_format($user->seeding_reputation, 2) }} reputation</strong>
                </div>
                    <p class="rank-intro">
                        Seeder Reputation determines your rank.
                        Reputation increases when you seed torrents for longer
                        periods and when you seed larger torrents.
                    </p>


                    <div class="rank-grid">

                        <div class="rank-box">
                            <div class="rank-icon">🌱</div>
                            <div class="rank-title">New Seeder</div>
                            <div class="rank-desc">
                                Starting rank for new users beginning their seeding journey.
                            </div>
                            <div class="rank-score">
                                0 – 100 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">🥉</div>
                            <div class="rank-title">Bronze</div>
                            <div class="rank-desc">
                                You have started contributing by seeding torrents.
                            </div>
                            <div class="rank-score">
                                101 – 300 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">🥈</div>
                            <div class="rank-title">Silver</div>
                            <div class="rank-desc">
                                Consistent seeder helping keep torrents alive.
                            </div>
                            <div class="rank-score">
                                301 – 600 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">🥇</div>
                            <div class="rank-title">Gold</div>
                            <div class="rank-desc">
                                Strong contributor with significant seeding activity.
                            </div>
                            <div class="rank-score">
                                601 – 1000 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">💎</div>
                            <div class="rank-title">Elite</div>
                            <div class="rank-desc">
                                Highly dedicated seeder supporting the tracker ecosystem.
                            </div>
                            <div class="rank-score">
                                1001 – 2000 reputation
                            </div>
                        </div>


                        <div class="rank-box">
                            <div class="rank-icon">👑</div>
                            <div class="rank-title">Legend</div>
                            <div class="rank-desc">
                                Top tier seeder with exceptional long-term contribution.
                            </div>
                            <div class="rank-score">
                                2001+ reputation
                            </div>
                        </div>

                    </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="seeder-guide-close" data-bs-dismiss="modal">Back to profile</button>
            </div>
        </div>
    </div>
</div>
<style>
.reputation-card { position: relative; }
.seeder-guide-trigger { display: flex; align-items: center; justify-content: space-between; gap: .75rem; width: 100%; margin-top: 1rem; padding: .75rem 0 0; border: 0; border-top: 1px solid #80e0cf26; background: transparent; color: #80e0cf; font: inherit; font-size: .9375rem; text-align: left; }
.seeder-guide-trigger::after { content: ''; position: absolute; inset: 0; border-radius: inherit; cursor: pointer; }
.seeder-guide-trigger:focus-visible { outline: none; }
.seeder-guide-trigger:focus-visible::after { outline: 2px solid #80e0cf; outline-offset: -3px; }
.reputation-card:has(.seeder-guide-trigger:hover) { border-color: #80e0cf88; }
.profile-guide-modal { color: #e9f0f7; line-height: 1.6; }
.profile-guide-modal .modal-content { background: #0b141d; border: 1px solid #354e58; border-radius: 20px; overflow: hidden; box-shadow: 0 24px 80px #0006; }
.profile-guide-modal .modal-header { padding: 1.5rem; border-bottom: 1px solid #2b3a4c; background: radial-gradient(ellipse at top right, #80e0cf18, transparent 75%), #101b24; gap: 1rem; }
.seeder-guide-eyebrow { color: #80e0cf; font-size: .875rem; }
.profile-guide-modal .modal-title { margin-top: .35rem; color: #f1f5f9; font-size: clamp(1.35rem, 3vw, 1.75rem); font-weight: 700; }
.profile-guide-modal .modal-body { padding: 1.5rem; }
.seeder-guide-current { display: flex; flex-wrap: wrap; justify-content: space-between; gap: .5rem 1rem; padding: 1rem; margin-bottom: 1.25rem; border: 1px solid #80e0cf26; background: #80e0cf08; border-radius: 12px; color: #80e0cf; }
.profile-guide-modal .rank-intro, .profile-guide-modal .rank-desc { color: #a5b4c7; }
.profile-guide-modal .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #2b3a4c; }
.seeder-guide-close { background: #80e0cf; color: #102c2b; border: 1px solid #80e0cf; border-radius: 9px; padding: .65rem 1rem; font: inherit; font-weight: 650; }
.seeder-guide-close:hover { background: #a3f0e2; }
.profile-guide-modal button:focus-visible { outline: 2px solid #80e0cf; outline-offset: 4px; }
@media(max-width: 575px) { .profile-guide-modal .modal-content { border-radius: 0; } .profile-guide-modal .modal-header, .profile-guide-modal .modal-body { padding: 1rem; } .profile-guide-modal .rank-grid { grid-template-columns: 1fr; } }
</style>
@push('scripts')
<script>
(() => {
    const modal = document.getElementById('seederRankModal');
    if (modal) document.body.appendChild(modal);
})();
</script>
@endpush
