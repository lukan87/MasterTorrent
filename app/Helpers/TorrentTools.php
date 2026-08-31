<?php


namespace App\Helpers;

class TorrentTools
{
    /**
     * Name of the file to be saved.
     */
    public static $fileName = '';

    /**
     * Representative table of the decoded torrent.
     */
    public static $torrentData = [];

    /**
     * Moves and decodes the torrent.
     * @param $torrentFile
     * @return array|int|string|void
     */
    public static function normalizeTorrent($torrentFile)
    {
        $result = Bencode::bdecode_file($torrentFile);
        // The PID will be set if an user downloads the torrent, but for
        // security purposes it's better to overwrite the user-provided
        // announce URL.
        $announce = config('app.url');
        $announce .= '/announce/PID';
        $result['announce'] = $announce;
        $result['info']['source'] = config('torrent.source');
        $result['info']['private'] = 1;
        $created_by = config('torrent.created_by', null);
        $created_by_append = config('torrent.created_by_append', false);
        if ($created_by !== null) {
            if ($created_by_append && array_key_exists('created by', $result)) {
                $c = $result['created by'];
                $c = trim($c, '. ');
                $c .= '. '.$created_by;
                $created_by = $c;
            }
            $result['created by'] = $created_by;
        }
        $comment = config('torrent.comment', null);
        if ($comment !== null) {
            $result['comment'] = $comment;
        }

        return $result;
    }

    /**
     * Calculate the number of files in the torrent.
     * @param $torrentData
     * @return int
     */
    public static function getFileCount($torrentData)
    {
        // Multiple file torrent ?
        if (array_key_exists('files', $torrentData['info']) && count($torrentData['info']['files'])) {
            return count($torrentData['info']['files']);
        }

        return 1;
    }

    /**
     * Returns the size of the torrent files.
     * @param $torrentData
     * @return int|mixed
     */
    public static function getTorrentSize($torrentData)
    {
        $size = 0;
        if (array_key_exists('files', $torrentData['info']) && count($torrentData['info']['files'])) {
            foreach ($torrentData['info']['files'] as $k => $file) {
                $dir = '';
                $size += $file['length'];
                $count = count($file['path']);
            }
        } else {
            $size = $torrentData['info']['length'];
            //$files[0] = $decodedTorrent['info']['name.utf-8'];
        }

        return $size;
    }

    /**
     * Returns the torrent file list.
     * @param $torrentData
     * @return mixed
     */
    public static function getTorrentFiles($torrentData)
    {
        if (array_key_exists('files', $torrentData['info']) && count($torrentData['info']['files'])) {
            foreach ($torrentData['info']['files'] as $k => $file) {
                $dir = '';
                $count = count($file['path']);
                for ($i = 0; $i < $count; $i++) {
                    if (($i + 1) == $count) {
                        $fname = $dir.$file['path'][$i];
                        $files[$k]['name'] = $fname;
                    } else {
                        $dir .= $file['path'][$i].'/';
                        $files[$k]['name'] = $dir;
                    }
                    $files[$k]['size'] = $file['length'];
                }
            }
        } else {
            $files[0]['name'] = $torrentData['info']['name'];
            $files[0]['size'] = $torrentData['info']['length'];
        }

        return $files;
    }

    /**
     * Returns the sha1 (hash) of the torrent.
     * @param $torrentData
     * @return string
     */
    public static function getTorrentHash($torrentData)
    {
        return sha1(Bencode::bencode($torrentData['info']));
    }

    /**
     * Returns the number of the torrent file.
     * @param $torrentData
     * @return int
     */
    public static function getTorrentFileCount($torrentData)
    {
        if (array_key_exists('files', $torrentData['info'])) {
            return count($torrentData['info']['files']);
        }

        return 1;
    }

    /**
     * Returns the NFO.
     * @param $inputFile
     * @return false|string|null
     */
    public static function getNfo($inputFile)
    {
        $fileName = uniqid().'.nfo';
        $inputFile->move(getcwd().'/files/tmp/', $fileName);
        if (file_exists(getcwd().'/files/tmp/'.$fileName)) {
            $fileContent = file_get_contents(getcwd().'/files/tmp/'.$fileName);
            unlink(getcwd().'/files/tmp/'.$fileName);
        } else {
            $fileContent = null;
        }

        return $fileContent;
    }
}
