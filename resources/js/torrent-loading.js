const active = new Set();

export function setTorrentLoading(source, loading) {
    if (loading) active.add(source);
    else active.delete(source);
    const indicator = document.querySelector('[data-torrent-loading]');
    if (indicator) indicator.hidden = active.size === 0;
}
