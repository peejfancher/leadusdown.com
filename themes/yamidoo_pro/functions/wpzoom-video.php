<?php

/**
 * This file is part of AutoEmbed.
 * http://autoembed.com
 *
 * $Id: AutoEmbed.class.php 204 2010-02-23 20:52:06Z phpuser $
 *
 * AutoEmbed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AutoEmbed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with AutoEmbed.  If not, see <http://www.gnu.org/licenses/>.
 */

class AutoEmbed {

  const AE_TAG = '';

  private $_media_id;
  private $_stub;
  private $_object_attribs;
  private $_object_params;

  /**
   * AutoEmbed Constructor
   *
   * @return object - AutoEmbed object
   */
  public function __construct() {
    global $AutoEmbed_stubs;
  }

  /**
   * Parse given URL
   *
   * @param $url string - href to check for embeded video
   *
   * @return boolean - whether or not the url contains valid/supported video
   */
  public function parseUrl($url) {
    global $AutoEmbed_stubs;

    foreach ($AutoEmbed_stubs as $stub) { 
      if ( preg_match('~'.$stub['url-match'].'~imu', $url, $match) ) {
        $this->_stub = $stub;

        if ( isset($stub['fetch-match'] ) ) {
          return $this->_parseLink($url);

        } else {
          $this->_media_id = $match;
          $this->_setDefaultParams();
          return true;
        }
      }
    }

    unset($stub);
    return false;
  }

  /**
   * Create the embed code for a local file
   *
   * @param $file string - the file we are wanting to embed
   *
   * @return boolean - whether or not the url contains valid/supported video
   */
  public function embedLocal($file) {
    return $this->parseUrl("__local__$file");
  }

  /**
   * Returns info about the stub
   *
   * @param string $property - (optional) the specific
   *           property of the stub to be returned.  If 
   *           ommited, array of all properties are returned
   *
   * @return mixed - details about the stub 
   */
  public function getStub($property = null) {
    return isset($property) ? $this->_stub[$property] : $this->_stub;
  }

  /**
   * Return object params about the video metadata
   *
   * @return array - object params
   */
  public function getObjectParams() {
    return $this->_object_params;
  }

  /**
   * Convert the url to an embedable tag
   *
   * return string - the embed html
   */
  public function getEmbedCode() {
    return $this->_buildObject();
  }

  /**
   * Return a thumbnail for the embeded video
   *
   * return string - the thumbnail href
   */
  public function getImageURL() {
    if (!isset($this->_stub['image-src'])) return false;

    $thumb = $this->_stub['image-src'];

    for ($i=1; $i<=count($this->_media_id); $i++) {
      $thumb = str_ireplace('$'.$i, $this->_media_id[$i - 1], $thumb);
    }

    return $thumb;
  }

  /**
   * Set the height of the object
   * 
   * @param mixed - height to set the object to
   *
   * @return boolean - true if the value was set, false
   *                   if parseURL hasn't been called yet
   */
  public function setHeight($height) {
    return $this->setObjectAttrib('height', $height);
  }

  /**
   * Set the width of the object
   * 
   * @param mixed - width to set the object to
   *
   * @return boolean - true if the value was set, false
   *                   if parseURL hasn't been called yet
   */
  public function setWidth($width) {
    return $this->setObjectAttrib('width', $width);
  }

  /**
   * Override a default param value for both the object
   * and flash param list
   *
   * @param $param mixed - the name of the param to be set
   *                       or an array of multiple params to set
   * @param $value string - (optional) the value to set the param to
   *                        if only one param is being set
   *
   * @return boolean - true if the value was set, false
   *                   if parseURL hasn't been called yet
   */
  public function setParam($param, $value = null) {
    return $this->setObjectParam($param, $value);
  }

  /**
   * Override a default object param value
   *
   * @param $param mixed - the name of the param to be set
   *                       or an array of multiple params to set
   * @param $value string - (optional) the value to set the param to
   *                        if only one param is being set
   *
   * @return boolean - true if the value was set, false
   *                   if parseURL hasn't been called yet
   */
  public function setObjectParam($param, $value = null) {
    if (!is_array($this->_object_params)) return false;

    if ( is_array($param) ) {
      foreach ($param as $p => $v) {
        $this->_object_params[$p] = $v;
      }

    } else {
      $this->_object_params[$param] = $value;
    }

    return true;
  }

  /**
   * Override a default object attribute value
   *
   * @param $param mixed - the name of the attribute to be set
   *                       or an array of multiple attribs to be set
   * @param $value string - (optional) the value to set the param to
   *                        if only one param is being set
   *
   * @return boolean - true if the value was set, false
   *                   if parseURL hasn't been called yet
   */
  public function setObjectAttrib($param, $value = null) {
    if (!is_array($this->_object_attribs)) return false;

    if ( is_array($param) ) {
      foreach ($param as $p => $v) {
        $this->_object_attribs[$p] = $v;
      }

    } else {
      $this->_object_attribs[$param] = $value;
    }

    return true;
  }

  /**
   * Attempt to parse the embed id from a given URL
   */ 
  private function _parseLink($url) {
    $source = preg_replace('/[^(\x20-\x7F)]*/','', file_get_contents($url));

    if ( preg_match('~'.$this->_stub['fetch-match'].'~imu', $source, $match) ) {
      $this->_media_id = $match;
      $this->_setDefaultParams();
      return true;
    }

    return false;
  }

  /**
   * Build a generic object skeleton 
   */
  private function _buildObject() {

    $object_attribs = $object_params = '';

    foreach ($this->_object_attribs as $param => $value) {
      $object_attribs .= '  ' . $param . '="' . $value . '"';    
    }

    foreach ($this->_object_params as $param => $value) {
      $object_params .= '<param name="' . $param . '" value="' . $value . '" />';
    }

    return sprintf("<object %s> %s  %s</object>", $object_attribs, $object_params, self::AE_TAG);
  }

  
  /**
   * Set the default params for the type of
   * stub we are working with
   */
  private function _setDefaultParams() {

    $source = $this->_stub['embed-src'];
    $flashvars = (isset($this->_stub['flashvars']))? $this->_stub['flashvars'] : null;

    for ($i=1; $i<=count($this->_media_id); $i++) {
      $source = str_ireplace('$'.$i, $this->_media_id[$i - 1], $source);
      $flashvars = str_ireplace('$'.$i, $this->_media_id[$i - 1], $flashvars);
    }

    $source = htmlspecialchars($source, ENT_QUOTES, null, false);
    $flashvars = htmlspecialchars($flashvars, ENT_QUOTES, null, false);

    $this->_object_params = array(
            'movie' => $source,
            'quality' => 'high',
            'allowFullScreen' => 'true',
            'allowScriptAccess' => 'always',
            'pluginspage' => 'http://www.macromedia.com/go/getflashplayer',
            'autoplay' => 'false',
            'autostart' => 'false',
            'flashvars' => $flashvars,
           );

    $this->_object_attribs = array(
            'type' => 'application/x-shockwave-flash',
            'data' => $source,
            'width' => $this->_stub['embed-width'],
            'height' => $this->_stub['embed-height'],
           );
  }

}

/**
 * This file is part of AutoEmbed.
 * http://autoembed.com
 *
 * $Id: stubs.php 204 2010-02-23 20:52:06Z phpuser $
 *
 * Some regular expressions found in this file were borrowed 
 * from Karl Benson & Rene-Gilles Deberdt.
 *
 * AutoEmbed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AutoEmbed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with AutoEmbed.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
  Example:
  array(
    'title'        =>  Source the embeded media comes from
    'website'      =>  URI of the media source
    'url-match'    =>  Regexp for matching the submitted url to a stub
    'embed-src'    =>  The source of the media to embed.  Replace $2, $3, etc with matches from the url-match or fetch-match regexp ($1 is the entire matched url)
    'embed-width'  =>  The default width of the embeded object
    'embed-height' =>  The default width of the embeded object
    'fetch-match'  => (optional) if set, html will be fetched and this regexp will be used to pull the media id or the source of the video
    'flashvars'    => (optional) if set, will be passed in the embed tag.  Replace $2, $3, etc with matches from url-match or fetch-match
  ),
*/
$AutoEmbed_stubs = array(
  array(
    'title' => 'YouTube',
    'website' => 'http://www.youtube.com',
    'url-match' => 'http://(?:video\.google\.(?:com|com\.au|co\.uk|de|es|fr|it|nl|pl|ca|cn)/(?:[^"]*?))?(?:(?:www|au|br|ca|es|fr|de|hk|ie|in|il|it|jp|kr|mx|nl|nz|pl|ru|tw|uk)\.)?youtube\.com(?:[^"]*?)?(?:&|&amp;|/|\?|;|\%3F|\%2F)(?:video_id=|v(?:/|=|\%3D|\%2F))([0-9a-z-_]{11})',
    'embed-src' => 'http://www.youtube.com/v/$2&rel=0&fs=1',
    'embed-width' => '425',
    'embed-height' => '344',
    'image-src' => 'http://img.youtube.com/vi/$2/0.jpg'
  ),
  array(
    'title' => 'YouTube (Playlists)',
    'website' => 'http://www.youtube.com',
    'url-match' => 'http://(?:(?:www|au|br|ca|es|fr|de|hk|ie|in|il|it|jp|kr|mx|nl|nz|pl|ru|tw|uk)\.)?youtube\.com(?:[^"]*?)?(?:&|&amp;|/|\?|;)(?:id=|p=|p/)([0-9a-f]{16})',
    'embed-src' => 'http://www.youtube.com/p/$2&rel=0&fs=1',
    'embed-width' => '480',
    'embed-height' => '385',
  ),
  array(
    'title' => 'Dailymotion',
    'website' => 'http://www.dailymotion.com',
    'url-match' => 'http://(?:www\.)?dailymotion\.(?:com|alice\.it)/(?:(?:[^"]*?)?video|swf)/([a-z0-9]{1,18})',
    'embed-src' => 'http://www.dailymotion.com/swf/$2&related=0',
    'embed-width' => '420',
    'embed-height' => '339',
    'image-src' => 'http://www.dailymotion.com/thumbnail/160x120/video/$2',
  ),
  array(
    'title' => 'Google Video',
    'website' => 'http://video.google.com',
    'url-match' => 'http://video\.google\.(com|com\.au|co\.uk|de|es|fr|it|nl|pl|ca|cn)/(?:videoplay|url|googleplayer\.swf)\?(?:[^"]*?)?docid=([0-9a-z-_]{1,20})',
    'embed-src' => 'http://video.google.$2/googleplayer.swf?docId=$3',
    'embed-width' => '400',
    'embed-height' => '326',
  ),
  array(
    'title' => 'MegaVideo',
    'website' => 'http://www.megavideo.com',
    'url-match' => 'http://(?:www\.)?megavideo\.com/(?:\?(?:[^"]*?)?v=|v/)([0-9a-z]{8})',
    'embed-src' => 'http://www.megavideo.com/v/$2.0.0',
    'embed-width' => '440',
    'embed-height' => '359',
  ),
  array(
    'title' => 'MetaCafe',
    'website' => 'http://www.metacafe.com',
    'url-match' => 'http://(?:www\.)?metacafe\.com/(?:watch|fplayer)/(\w{1,10})/',
    'embed-src' => 'http://www.metacafe.com/fplayer/$2/metacafe.swf',
    'embed-width' => '400',
    'embed-height' => '345',
  ),
  array(
    'title' => 'Revver',
    'website' => 'http://www.revver.com',
    'url-match' => 'http://(?:one\.|www\.)?revver\.com/(?:watch|video)/([0-9]{1,8})',
    'embed-src' => 'http://flash.revver.com/player/1.0/player.swf?mediaId=$2',
    'embed-width' => '480',
    'embed-height' => '392',
  ),
  array(
    'title' => 'Vimeo',
    'website' => 'http://www.vimeo.com',
    'url-match' => 'http://(?:www\.)?vimeo\.com/([0-9]{1,12})',
    'embed-src' => 'http://vimeo.com/moogaloop.swf?clip_id=$2&server=vimeo.com&fullscreen=1&show_title=1&show_byline=1&show_portrait=0&color=01AAEA',
    'embed-width' => '400',
    'embed-height' => '302',
  ),
  array(
    'title' => '123video',
    'website' => 'http://www.123video.nl',
    'url-match' => 'http://(?:www\.)?123video\.nl/(?:playvideos\.asp\?(?:[^"]*?)?MovieID=|123video_share\.swf\?mediaSrc=)([0-9]{1,8})',
    'embed-src' => 'http://www.123video.nl/123video_share.swf?mediaSrc=$2',
    'embed-width' => '420',
    'embed-height' => '339',
  ),
  array(
    'title' => '5min Life Videopedia',
    'website' => 'http://www.5min.com',
    'url-match' => 'http://(?:www\.)?5min\.com/(?:Embeded/|Video/(?:[0-9a-z_-]*?)?-)([0-9]{8})',
    'embed-src' => 'http://www.5min.com/Embeded/$2/',
    'embed-width' => '425',
    'embed-height' => '355',
  ),
  array(
    'title' => 'AdultSwim',
    'website' => 'http://www.adultswim.com',
    'url-match' => 'http://www\.adultswim\.com/video/(?:vplayer/index\.html\?id=|\?episodeID=|ASVPlayer\.swf\?id=)([0-9a-f]{32})',
    'embed-src' => 'http://www.adultswim.com/video/vplayer/index.html?id=$2',
    'embed-width' => '425',
    'embed-height' => '355',
  ),
  array(
    'title' => 'AniBoom',
    'website' => 'http://www.aniboom.com',
    'url-match' => 'http://(?:www\.|api\.)?aniboom\.com/(?:Player.aspx\?(?:[^"]*?)?v=|video/|e/)([0-9]{1,10})',
    'embed-src' => 'http://api.aniboom.com/e/$2',
    'embed-width' => '425',
    'embed-height' => '355',
  ),
  array(
    'title' => 'AOL Video (Old)',
    'website' => 'http://video.aol.com',
    'url-match' => 'http://video\.aol\.com/partner/([a-z0-9-_]+)/([a-z0-9-_]+)/([a-z0-9:\.]+)',
    'embed-src' => 'http://media.mtvnservices.com/$4',
    'embed-width' => '415',
    'embed-height' => '347',
  ),
  array(
    'title' => 'Archive.org',
    'website' => 'http://www.archive.org',
    'url-match' => 'http://(?:www\.)?archive\.org/download/((?:[0-9a-z_-]*?)/(?:[0-9a-z_-]*?)\.flv)',
    'embed-src' => 'http://www.archive.org/flow/FlowPlayerLight.swf?config=%7Bembedded%3Atrue%2CshowFullScreenButton%3Atrue%2CshowMuteVolumeButton%3Atrue%2CshowMenu%3Atrue%2CautoBuffering%3Afalse%2CautoPlay%3Afalse%2CinitialScale%3A%27fit%27%2CmenuItems%3A%5Bfalse%2Cfalse%2Cfalse%2Cfalse%2Ctrue%2Ctrue%2Cfalse%5D%2CusePlayOverlay%3Afalse%2CshowPlayListButtons%3Atrue%2CplayList%3A%5B%7Burl%3A%27$2%27%7D%5D%2CcontrolBarGloss%3A%27high%27%2CshowVolumeSlider%3Atrue%2CbaseURL%3A%27http%3A%2F%2Fwww%2Earchive%2Eorg%2Fdownload%2F%27%2Cloop%3Afalse%2CcontrolBarBackgroundColor%3A%270x000000%27%7D',
    'embed-width' => '480',
    'embed-height' => '360',
  ),
  array(
    'title' => 'Atom',
    'website' => 'http://www.atom.com',
    'url-match' => 'http://(?:www\.)?atom\.com/funny_videos/([A-z0-9-_]*)/',
    'fetch-match' => '<embed src="([A-z:/\.0-9-_=]*)"',
    'embed-src' => '$2',
    'embed-width' => '425',
    'embed-height' => '354',
  ),
  array(
    'title' => 'Island Tickle Video',
    'website' => 'http://www.islandticklevideo.com',
    'url-match' => 'http://(?:www\.)?islandticklevideo\.com/mediashare/video/(.*)',
    'fetch-match' => 'http://www\.islandticklevideo\.com/mediashare/modules/vPlayer/vPlayercfg\.php\?id=([a-z0-9]{10,25})',
    'embed-src' => 'http://www.islandticklevideo.com/mediashare/modules/vPlayer/vPlayer.swf?f=http://www.islandticklevideo.com/mediashare/modules/vPlayer/vPlayercfg.php?id=$2',
    'embed-width' => '480',
    'embed-height' => '385',
  ),
  array(
    'title' => 'Bebo',
    'website' => 'http://www.bebo.com',
    'url-match' => '(http://bebo\.(?:[0-9]{1,4})\.download\.videoegg\.com(?:(?:/(?:[0-9a-z]*)){5}))',
    'embed-src' => 'http://static.videoegg.com/videoegg/loader.swf?file=$2',
    'embed-width' => '425',
    'embed-height' => '350',
  ),
  array(
    'title' => 'Blastro',
    'website' => 'http://www.blastro.com',
    'url-match' => 'http://(?:www\.)?blastro\.com/player/([a-z0-9-_]*)\.html',
    'embed-src' => 'http://images.blastro.com/images/flashplayer/flvPlayer.swf?site=www.blastro.com&amp;filename=$2',
    'embed-width' => '512',
    'embed-height' => '408',
  ),
  
    array(
    'title' => 'Blip',
    'website' => 'http://www.blip.tv',
    'url-match' => 'http://blip\.tv/(play|file)/([0-9]*)',
    //'url-match' => 'http://(?:www\.|(?:[a-z0-9]*?)\.)?blip\.tv/(play|file)/(\w{1,15})',
    'fetch-match' => '<link rel="video_src" href="([A-z:/\.0-9-_=]*)',
    //'fetch-match' => '<link rel="video_src" href="(.*flash\/[0-9]{1,10})',
    'embed-src' => '$2',
    //'embed-src' => '<iframe src="$1#video_player" width="625"height="500" frameborder="0" scrolling="no"><a href="$1">Go to site</a></iframe>',
    'embed-width' => '500',
    'embed-height' => '315',
  ), 
  
  
 array(
   'title' => 'BNQT',
   'website' => 'http://www.bnqt.tv',
   'url-match' => 'http://(?:www\.)?bnqt\.com/videos/detail/.*/([0-9]{10,18})',
   'embed-src' => 'http://www.bnqt.com/bnqtPlayer/vid_$2',
   'embed-width' => '480',
   'embed-height' => '294',
  ),
 
  array(
    'title' => 'BoFunk',
    'website' => 'http://www.bofunk.com',
    'url-match' => 'http://(?:www\.)?bofunk\.com/video/[0-9]{2,7}/',
    'fetch-match' => '<embed src="/[a-z]/([a-z:/\.0-9-_=?%]*)"',
    'embed-src' => 'http://www.bofunk.com/e/$2',
    'embed-width' => '446',
    'embed-height' => '370',
  ),
  array(
    'title' => 'Break',
    'website' => 'http://www.break.com/',
    'url-match' => 'http://(?:www\.)?break\.com/(?:index|usercontent)/',
    'fetch-match' => 'http://embed\.break\.com/([0-9a-z]{1,8})',
    'embed-src' => '$1',
    'embed-width' => '464',
    'embed-height' => '383',
  ),
  array(
    'title' => 'Brightcove.com',
    'website' => 'http://link.brightcove.com',
    'url-match' => 'http://link\.brightcove\.com/services/link/bcpid([0-9]+)/bctid([0-9]+)',
    'embed-src' => 'http://services.brightcove.com/services/viewer/federated_f8/$2?videoId=$3&playerId=$2&viewerSecureGatewayURL=https://console.brightcove.com/services/amfgateway&servicesURL=http://services.brightcove.com/services&cdnURL=http://admin.brightcove.com&domain=embed&autoStart=false&',
    'embed-width' => '486',
    'embed-height' => '412',
  ),
  array(
    'title' => 'CBS News',
    'website' => 'http://www.cbsnews.com/video',
    'url-match' => 'http://(?:www\.)?cbsnews\.com/video/watch/',
    'fetch-match' => 'CBSVideo\.setVideoId\(.([a-z0-9-_]{1,32}).\)',
    'embed-src' => 'http://cnettv.cnet.com/av/video/cbsnews/atlantis2/player-dest.swf',
    'embed-width' => '425',
    'embed-height' => '324',
    'flashvars' => 'tag=contentBody;housing&releaseURL=http://cnettv.cnet.com/av/video/cbsnews/atlantis2/player-dest.swf&videoId=$2&partner=news&vert=News&autoPlayVid=false&name=cbsPlayer&allowScriptAccess=always&wmode=transparent&embedded=y&scale=noscale&rv=n&salign=tl'
  ),
  array(
    'title' => 'Cellfish',
    'website' => 'http://www.cellfish.com',
    'url-match' => 'http://(cellfish\.|www\.)?cellfish\.com/(?:video|ringtone|multimedia)/([0-9]{1,10})/',
    'embed-src' => 'http://$2cellfish.com/static/swf/player8.swf?Id=$3',
    'embed-width' => '420',
    'embed-height' => '315',
  ),
  array(
    'title' => 'Clarin',
    'website' => 'http://www.videos.clarin.com',
    'url-match' => 'http://(?:www\.)videos\.clarin\.com/index\.html\?id=([0-9]{1,12})',
    'embed-src' => 'http://www.clarin.com/shared/v9/swf/clarinvideos/player.swf',
    'embed-width' => '533',
    'embed-height' => '438',
    'flashvars' => 'autoplay=false&amp;SEARCH=http://www.videos.clarin.com/decoder/buscador_getMtmYRelacionados/$2|CLARIN_VIDEOS|VIDEO|EMBEDDED|10|1|10|null.xml&amp;RUTAS=http://www.clarin.com/shared/v9/swf/clarinvideos/rutas.xml',
  ),
  array(
    'title' => 'Clip.vn',
    'website' => 'http://www.clip.vn',
    'url-match' => 'http://(?:www\.)?clip\.vn/w(?:atch/(?:[a-z0-9-_]*?))?/([a-z0-9_-]{1,5}),vn',
    'embed-src' => 'http://www.clip.vn/w/$2,vn,0,,hq',
    'embed-width' => '448',
    'embed-height' => '361',
  ),
  array(
    'title' => 'ClipFish (Old)',
    'website' => 'http://www.clipfish.de',
    'url-match' => 'http://(?:www\.)?clipfish\.de/(?:(?:player\.php|videoplayer\.swf)\?(?:[^"]*?)?vid=|video/)([0-9]{1,20})',
    'embed-src' => 'http://www.clipfish.de/videoplayer.swf?as=0&vid=$2&r=1',
    'embed-width' => '464',
    'embed-height' => '380',
  ),
  array(
    'title' => 'ClipFish (New)',
    'website' => 'http://www.clipfish.de',
    'url-match' => 'http://(?:www\.)?clipfish\.de/(?:video)?player\.(?:swf|php)(?:[^"]*?)videoid=((?:[a-z0-9]{18})(?:==)?|(?:[a-z0-9]{6})(?:==)?)',
    'embed-src' => 'http://www.clipfish.de/videoplayer.swf?as=0&videoid=$2%3D%3D&r=1',
    'embed-width' => '464',
    'embed-height' => '380',
  ),
  array(
    'title' => 'ClipJunkie',
    'website' => 'http://www.clipjunkie.com',
    'url-match' => 'http://(?:www\.)?clipjunkie\.com/((?:[^"]*?)-vid(?:[0-9]{1,10}))\.html',
    'embed-src' => 'http://www.clipjunkie.com/flvplayer/flvplayer.swf?flv=http://videos.clipjunkie.com/videos/$2.flv&themes=http://www.clipjunkie.com/flvplayer/themes.xml&playList=http://www.clipjunkie.com/playlist.php&config=http://www.clipjunkie.com/skin/config.xml',
    'embed-width' => '460',
    'embed-height' => '357',
  ),
  array(
    'title' => 'ClipMoon',
    'website' => 'http://www.clipmoon.com',
    'url-match' => 'http://(?:www\.)?clipmoon\.com/(?:videos/|(?:[^"]*?)viewkey=)([0-9a-z]{1,10})',
    'embed-src' => 'http://www.clipmoon.com/flvplayer.swf?config=http://www.clipmoon.com/flvplayer.php?viewkey=$2&external=yes',
    'embed-width' => '460',
    'embed-height' => '357',
  ),
  array(
    'title' => 'Clipser',
    'website' => 'http://www.clipser.com',
    'url-match' => 'http://(?:www\.)?clipser\.com/(?:Play\?vid=|watch_video/)([0-9]{4,10})',
    'embed-src' => 'http://www.clipser.com/Play?vid=$2',
    'embed-width' => '425',
    'embed-height' => '355',
  ),
  array(
    'title' => 'ClipShack',
    'website' => 'http://www.clipshack.com',
    'url-match' => 'http://(?:www\.)?clipshack\.com/Clip\.aspx\?key=([0-9a-f]{16})',
    'embed-src' => 'http://www.clipshack.com/player.swf?key=$2',
    'embed-width' => '430',
    'embed-height' => '370',
  ),
  array(
    'title' => 'CNetTV',
    'website' => 'http://cnettv.cnet.com',
    'url-match' => 'http://cnettv\.cnet\.com/[a-z0-9\-]*\/[0-9]{4}-[0-9]_[0-9]{2}-([0-9]{5,9})\.html',
    'embed-src' => 'http://www.cnet.com/av/video/flv/universalPlayer/universalSmall.swf',
    'embed-width' => '364',
    'embed-height' => '280',
    'flashvars' => 'playerType=embedded&type=id&value=$2',
  ),
  array(
    'title' => 'CollegeHumor',
    'website' => 'http://www.collegehumour.com',
    'title' => 'CollegeHumor',
    'url-match' => 'http://(?:www\.)?collegehumor\.com/video:([0-9]{1,12})',
    'embed-src' => 'http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=$2',
    'embed-width' => '480',
    'embed-height' => '360',
  ),
  array(
    'title' => 'TheDailyShow',
    'website' => 'http://www.thedailyshow.com',
    'url-match' => 'http://(?:www\.)?thedailyshow\.com/(?:watch|full\-episodes)',
    'fetch-match' => 'swfo.embedSWF\(.*(http://media.mtvnservices.com/mgid:cms:(video|fullepisode):comedycentral\.com:[0-9]{1,10})',
    'embed-src' => '$2',
    'embed-width' => '360',
    'embed-height' => '301',
  ),
  array(
    'title' => 'ColbertNation',
    'website' => 'http://www.colbertnation.com',
    'url-match' => 'http:\/\/(?:www\.)?colbertnation\.com\/the-colbert-report-videos\/([0-9]*)\/',
    'embed-src' => 'http://media.mtvnservices.com/mgid:cms:item:comedycentral.com:$2',
    'embed-width' => '360',
    'embed-height' => '301',
  ),
  array(
    'title' => 'Crackle',
    'website' => 'http://www.crackle.com',
    'url-match' => 'http://(?:www\.)?crackle\.com/c/([a-z0-9_]*?)/([a-z0-9_]*?)/([0-9]{1,10})',
    'embed-src' => 'http://www.crackle.com/p/$2/$3.swf?id=$4',
    'embed-width' => '400',
    'embed-height' => '328',
  ),
  array(
    'title' => 'CrunchyRoll',
    'website' => 'http://www.crunchyroll.com',
    'url-match' => 'http://(?:www\.)?crunchyroll\.com/getitem\?ih=([0-9a-z]{19})&(?:amp;)?videoid=([0-9]{1,12})&(?:amp;)?mediaid=([0-9]{1,12})&(?:amp;)?hash=([0-9a-z]{19})',
    'embed-src' => ' http://www.crunchyroll.com/flash/20080910153703.043ec803b06cc356a1e15c1184831a24/oldplayer2.swf?file=http%3A%2F%2Fwww.crunchyroll.com%2Fgetitem%3Fih%3D$2%26videoid%3D$3%26mediaid%3D$4%26hash%3D$5&autostart=false',
    'embed-width' => '576',
    'embed-height' => '325',
  ),
  array(
    'title' => 'Current',
    'website' => 'http://www.current.com',
    'url-match' => 'http://(?:www\.)?current\.com/items/([0-9]{8})',
    'embed-src' => 'http://current.com/e/$2/en_US',
    'embed-width' => '400',
    'embed-height' => '400',
  ),
  array(
    'title' => 'Dailyhaha',
    'website' => 'http://www.dailyhaha.com',
    'url-match' => 'http://(?:www\.)?dailyhaha\.com/_vids/(?:Whohah\.swf\?Vid=)?([a-z0-9_-]*?)\.(?:htm|flv)',
    'embed-src' => 'http://www.dailyhaha.com/_vids/Whohah.swf?Vid=$2.flv',
    'embed-width' => '425',
    'embed-height' => '350',
),
  array(
    'title' => 'Dave.tv',
    'website' => 'http://www.dave.tv',
    'url-match' => 'http://(?:www\.)?dave\.tv/MediaPlayer.aspx\?(?:[^"]*?)?contentItemId=([0-9]{1,10})',
    'embed-src' => 'http://dave.tv/dbox/dbox_small.swf?configURI=http://dave.tv/dbox/config.ashx&volume=50&channelContentId=$2',
    'embed-width' => '300',
    'embed-height' => '260',
  ),
  array(
    'title' => 'DotSub (w/o Captions)',
    'website' => 'http://www.dotsub.com',
    'url-match' => 'http://(?:www\.)?dotsub\.com/(?:media/|view/)((?:[0-9a-z]{8})(?:(?:-(?:[0-9a-z]{4})){3})-(?:[0-9a-z]{12}))',
    'embed-src' => 'http://dotsub.com/static/players/embed8l.swf?mediauri=http://dotsub.com/media/$2/em/flv/en',
    'embed-width' => '480',
    'embed-height' => '392',
  ),
  array(
    'title' => 'DoubleViking',
    'website' => 'http://www.doubleviking.com',
    'url-match' => 'http://(?:www\.)?doubleviking\.com/videos/page[0-9]\.html/[a-z\-]*[0-9]{1,8}\.html',
    'embed-src' => 'http://www.doubleviking.com/mediaplayer.swf?file=$2',
    'embed-width' => '400',
    'embed-height' => '340',
  ),
  array(
    'title' => 'Dropshots',
    'website' => 'http://www.dropshots.com',
    'title' => 'dropshots.com',
    'url-match' => '(http://media(?:[0-9]{0,2})\.dropshots\.com/photos(?:(?:/(?:[0-9]{1,10})){1,3})\.flv)',
    'embed-src' => 'http://www.dropshots.com/dropshotsplayer.swf?url=$2',
    'embed-width' => '480',
    'embed-height' => '385',
  ),
  array(
    'title' => 'Dv.ouou',
    'website' => 'http://dv.ouou.com',
    'url-match' => 'http://dv\.ouou\.com/(?:play/v_|v/)([a-f0-9]{14})',
    'embed-src' => 'http://dv.ouou.com/v/$2',
    'embed-width' => '480',
    'embed-height' => '385',
  ),
  array(
    'title' => 'Divshare',
    'website' => 'http://www.divshare.com',
    'url-match' => 'http://www\.divshare\.com/download/([^"]*)',
    'embed-src' => 'http://www.divshare.com/flash/playlist?myId=$2',
    'embed-width' => '335',
    'embed-height' => '28',
  ),
  array(
    'title' => 'EASportsWorld',
    'website' => 'http://www.easportsworld.com',
    'url-match' => '(http://videocdn\.easw\.easports\.com/easportsworld/media/(?:[0-9]{1,12})/(?:[0-9a-z-_]*?)\.flv)',
    'embed-src' => 'http://ll-999.ea.com/sonet-easw/2.2.4.0/flash/sw/videos/mediaplayer.swf?file=$2&image=http://ll-999.ea.com/sonet-easw/2.2.4.0/images/sw/videos/preview.jpg&backcolor=0x000000&frontcolor=0x006BCC&lightcolor=0x006BCC',
    'embed-width' => '566',
    'embed-height' => '355',
  ),
  array(
    'title' => 'EbaumsWorld',
    'website' => 'http://www.ebaumsworld.com',
    'url-match' => 'http://www\.ebaumsworld\.com/(?:video|audio)/(?:watch|play)',
    'fetch-match' => 'id="embed".*flashvars=&quot;(.*)&quot;\ wmode',
    'embed-src' => 'http://www.ebaumsworld.com/mediaplayer.swf',
    'embed-width' => '425',
    'embed-height' => '345',
    'flashvars' => '$2',
  ),
  array(
    'title' => 'ESPN',
    'website' => 'http://www.espn.com',
    'url-match' => 'http:\/\/espn\.go\.com\/video\/clip\?id=([0-9a-z]*)',
    'embed-src' => 'http://espn.go.com/videohub/player/embed.swf',
    'embed-width' => '384',
    'embed-height' => '216',
    'flashvars' => 'id=$2',
  ),
  array(
    'title' => 'Fandome',
    'website' => 'http://www.fandome.com',
    'url-match' => 'http://[a-z]*\.fandome\.com/video/([0-9]{3,6})/[a-z0-9\-_]*/',
    'embed-src' => 'http://www.kaltura.com/index.php/kwidget/wid/_35168/uiconf_id/1070752',
    'embed-width' => '480',
    'embed-height' => '380',
    'flashvars' => 'entryId=http://s3.amazonaws.com/lazyjock/$2.flv&amp;autoplay=false',
  ),
  array(
    'title' => 'Flickr',
    'website' => 'http://www.flickr.com',
    'url-match' => 'http://(?:www\.|www2\.)?flickr\.com/photos/[a-z0-9-_]*/([0-9]{8,12})',
    'fetch-match' => 'id="stewart_swf([0-9]{8,12})_div"',
    'embed-src' => 'http://www.flickr.com/apps/video/stewart.swf',
    'embed-width' => '400',
    'embed-height' => '300',
    'flashvars' => 'intl_lang=en-us&amp;photo_id=$2',
  ),
  array(
    'title' => 'FunnyOrDie',
    'website' => 'http://www.funnyordie.com',
    'url-match' => 'http://(?:www\.|www2\.)?funnyordie\.com/(?:videos/|public/flash/fodplayer\.swf\?key=)([0-9a-z]{8,12})',
    'embed-src' => 'http://player.ordienetworks.com/flash/fodplayer.swf',
    'embed-width' => '464',
    'embed-height' => '388',
    'flashvars' => 'key=$2',
  ),
  array(
    'title' => 'FunMansion',
    'website' => 'http://www.funmansion.com',
    'url-match' => 'http://www\.funmansion\.com/videos/[a-z0-9-_]*\.html',
    'fetch-match' => '<iframe src="http://media\.funmansion\.com/funmansion/player/player\.php\?url=([a-z0-9:/\.-_]*\.flv)',
    'embed-src' => 'http://media.funmansion.com/funmansion/player/flvplayer.swf?flv=$2',
    'embed-width' => '446',
    'embed-height' => '350',
  ),
  array(
    'title' => 'G4TV',
    'website' => 'http://www.g4tv.com',
    'url-match' => 'http://(?:www\.)?g4tv\.com/(?:xplay/videos/|lv3/|sv3/)([0-9]{1,10})',
    'embed-src' => 'http://www.g4tv.com/lv3/$2',
    'embed-width' => '480',
    'embed-height' => '418',
  ),
  array(
    'title' => 'GameKyo',
    'website' => 'http://www.gamekyo.com',
    'url-match' => 'http://(?:www\.)?gamekyo\.com/(?:video|flash/flvplayer\.swf\?videoid=)[a-z]{2}([0-9]{1,7})',
    'embed-src' => 'http://www.gamekyo.com/flash/flvplayer.swf?videoid=$2',
    'embed-width' => '512',
    'embed-height' => '307',
),
  array(
    'title' => 'GameSpot',
    'website' => 'http://www.gamespot.com',
    'url-match' => 'http://(?:(?:[a-z]*?)\.)?gamespot\.com/(?:[^"]*?)video/(?:(?:[0-9]{1,12})/)?([0-9]{1,12})',
    'embed-src' => 'http://image.com.com/gamespot/images/cne_flash/production/media_player/proteus/one/proteus2.swf',
    'embed-width' => '432',
    'embed-height' => '362',
    'flashvars' => 'skin=http://image.com.com/gamespot/images/cne_flash/production/media_player/proteus/one/skins/gamespot.png&paramsURI=http%3A%2F%2Fwww.gamespot.com%2Fpages%2Fvideo_player%2Fxml.php%3Fid%3D$2%26mode%3Dembedded%26width%3D432%26height%3D362%2F',
),
  array(
    'title' => 'GameTrailers (Inc. User Movies)',
    'website' => 'http://www.gametrailers.com',
    'url-match' => 'http://(?:www\.)?gametrailers\.com/(?:player/(u)?(?:sermovies/)?|remote_wrap\.php\?(u)?mid=)([0-9]{1,10})',
    'embed-src' => 'http://www.gametrailers.com/remote_wrap.php?$2$3mid=$4', //Either $2 or $3 will be empty
    'embed-width' => '480',
    'embed-height' => '392',
  ),
  array(
    'title' => 'GameTube',
    'website' => 'http://www.gametube.org',
    'title' => 'Gametube.org',
    'url-match' => 'http://(?:www\.)?gametube\.org/(?:\#/video/|htmlVideo\.jsp\?id=|miniPlayer\.swf\?vidId=)([A-z0-9=_-]{28})',
    'embed-src' => 'http://www.gametube.org/miniPlayer.swf?vidId=$2',
    'embed-width' => '425',
    'embed-height' => '335',
  ),
  array(
    'title' => 'GameVideos.1up',
    'website' => 'http://www.gamevideos.1up.com',
    'url-match' => 'http://(?:www\.)?gamevideos(?:\.1up)?\.com/(?:video/id/|video/embed\?(?:[^"]*?)?video=)([0-9]{1,8})',
    'embed-src' => 'http://gamevideos.1up.com/swf/gamevideos11.swf?embedded=1&fullscreen=1&autoplay=0&src=http://gamevideos.1up.com/video/videoListXML%3Fid%3D$2%26adPlay%3Dfalse',
    'embed-width' => '500',
    'embed-height' => '319',
  ),
  array(
    'title' => 'GarageTv',
    'website' => 'http://www.garagetv.be',
    'url-match' => '(http://www\.garagetv\.be/v/(?:[0-9a-z-\!_]*?)/v\.aspx)',
    'embed-src' => '$2',
    'embed-width' => '430',
    'embed-height' => '369',
  ),
  array(
    'title' => 'Gloria',
    'website' => 'http://www.gloria.tv',
    'url-match' => 'http://(?:www\.)?gloria\.tv/\?video=([a-z0-9]{20})',
    'embed-src' => 'http://www.gloria.tv/flvplayer.swf?file=http%3A%2F%2Fwww.gloria.tv%2F%3Fembed%26video%3D$2%26width%3D512%26height%3D288&type=flv&image=http%3A%2F%2Fwww.gloria.tv%2F%3Fembed%26image%3D$2%26width%3D512%26height%3D288&autostart=false&showdigits=true&usefullscreen=false&logo=http%3A%2F%2Fwww.gloria.tv%2Fimage%2Flogo_embed.png&link=http%3A%2F%2Fwww.gloria.tv%2F%3Fvideo%3Dddexrl6eelym3gaabxmz%26amp%3Bview%3Dflash&linktarget=_blank&volume=100&backcolor=0xe0e0e0&frontcolor=0x000000&lightcolor=0xf00000',
    'embed-width' => '512',
    'embed-height' => '404',
  ),
  array(
    'title' => 'GoEar',
    'website' => 'http://www.goear.com',
    'url-match' => 'http://(?:www\.)?goear\.com/listen\.php\?v=([a-z0-9]{7})',
    'embed-src' => 'http://www.goear.com/files/external.swf?file=$2',
    'embed-width' => '353',
    'embed-height' => '132',
  ),
  array(
    'title' => 'Good.IS',
    'website' => 'http://www.good.is',
    'url-match' => 'http://www\.good\.is/\?p=([0-9]{3,7})',
    'fetch-match' => '(http:\/\/s3\.amazonaws\.com\/.*Url=http:\/\/www\.good\.is\/\?p=[0-9]{3,7})&quot;\/&gt;&lt;embed src=&',
    'embed-src' => '$2',
    'embed-width' => '416',
    'embed-height' => '264',
    'flashvars' => '$2',
  ),

  array(
    'title' => 'Glumbert',
    'website' => 'http://www.glumbert.com',
    'url-match' => 'http://(?:www\.)?glumbert\.com/(?:embed|media)/([a-z0-9_-]{1,30})',
    'embed-src' => 'http://www.glumbert.com/embed/$2',
    'embed-width' => '425',
    'embed-height' => '335',
  ),
  array(
    'title' => 'GodTube',
    'website' => 'http://www.godtube.com',
    'url-match' => 'http://(?:www\.)?godtube\.com/view_video\.php\?(?:[^"]*?)?viewkey=([0-9a-f]{20})',
    'embed-src' => 'http://godtube.com/flvplayer.swf?viewkey=$2',
    'embed-width' => '330',
    'embed-height' => '270',
  ),
  array(
    'title' => 'GrindTv',
    'website' => 'http://www.grindtv.com',
    'url-match' => '(http://(?:www\.)?grindtv\.com/video/(.*)/(?:[^"]*?)i=(?:[0-9]{1,12}))',
    'embed-src' => 'http://images.grindtv.com/1.0.2/swf/video.swf?sa=1&si=1&i=$3&sct=$2',
    'embed-width' => '640',
    'embed-height' => '480',
  ),
  array(
    'title' => 'Guzer',
    'website' => 'http://www.guzer.com',
    'url-match' => 'http://(?:www\.)?guzer\.com/videos/(.*).php',
    'embed-src' => 'http://www.guzer.com/player/mediaplayer.swf',
    'embed-width' => '486',
    'embed-height' => '382',
    'flashvars' => 'height=382&amp;width=486&amp;file=http://media.guzer.com/videos/$2.flv&amp;image=http://www.guzer.com/videos/s$2.jpg'
  ),
  array(
    'title' => 'TheHub',
    'website' => 'http://hub.witness.org',
    'url-match' => 'http://hub\.witness\.org/(?:en|fr|es)/node/([0-9]{1,10})',
    'embed-src' => 'http://hub.witness.org/sites/hub.witness.org/modules/contrib-5/flvmediaplayer/mediaplayer.swf?file=http://hub.witness.org/xspf/node/$2&overstretch=fit&repeat=false&logo=http://hub.witness.org/sites/hub.witness.org/themes/witness/images/hub_wm.png',
    'embed-width' => '320',
    'embed-height' => '260',
  ),
  array(
    'title' => 'Howcast',
    'website' => 'http://www.howcast.com',
    'url-match' => 'http://(?:www\.)?howcast\.com/videos/([0-9]{1,8})',
    'embed-src' => 'http://www.howcast.com/flash/howcast_player.swf?file=$2&theme=black',
    'embed-width' => '432',
    'embed-height' => '276',
  ),
  array(
    'title' => 'Hulu (Usa Only)',
    'website' => 'http://www.hulu.com',
    'url-match' => 'http://(?:www\.)?hulu\.com/watch/(?:[0-9]{1,8})/',
    'fetch-match' => '<link rel="video_src" href="([A-z:/\.0-9-_=?]*)',
    'embed-src' => '$2',
    'embed-width' => '512',
    'embed-height' => '296',
  ),
  array(
    'title' => 'Humour',
    'website' => 'http://www.humour.com',
    'url-match' => 'http://(?:video|www)\.humour\.com/videos-comiques/videos.asp\?[A-z]*\=([1-9]{1,8})',
    'embed-src' => '/videos-comiques/player/mediaplayer.swf',
    'embed-width' => '425',
    'embed-height' => '355',
  ),
  array(
    'title' => 'Video.i.ua',
    'website' => 'http://video.i.ua',
    'url-match' => '(http://i1\.i\.ua/video/vp3\.swf\?9&(?:amp;)?userID=(?:[0-9]{1,20})&(?:amp;)?videoID=(?:[0-9]{1,20})&(?:amp;)?playTime=(?:[0-9]{1,20})&(?:amp;)?repeat=0&(?:amp;)?autostart=0&(?:amp;)?videoSize=(?:[0-9]{1,20})&(?:amp;)?userStatus=(?:[0-9]{1,2})&(?:amp;)?notPreview=(?:[0-9]{1,2})&(?:amp;)?mID=m?(?:[0-9]{1,2}))',
    'embed-src' => '$2',
    'embed-width' => '450',
    'embed-height' => '349',
  ),
  array(
    'title' => 'IGN',
    'website' => 'http://www.ign.com',
    'url-match' => 'http://(?:(?:(?:[a-z0-9]*?)\.){0,3})ign\.com/(?:[a-z0-9-_]*?)?/objects/([0-9]{1,10})/(?:(?:[a-z0-9-_]*?)/)?videos/',
    'embed-src' => 'http://videomedia.ign.com/ev/ev.swf?object_ID=$2',
    'embed-width' => '433',
    'embed-height' => '360',
  ),
  array(
    'title' => 'iJigg',
    'website' => 'http://www.ijigg.com',
    'url-match' => 'http://(?:www\.)?ijigg\.com/(?:jiggPlayer\.swf\?songID=|songs/|trackback/)([0-9A-Z]{9,12})',
    'embed-src' => 'http://www.ijigg.com/jiggPlayer.swf?songID=$2&Autoplay=0',
    'embed-width' => '315',
    'embed-height' => '80',
  ),
  array(
    'title' => 'IMDB',
    'website' => 'http://www.imdb.com',
    'url-match' => 'http://(?:www\.)?totaleclips\.com/Player/Bounce\.aspx\?eclipid=([0-9a-z]{1,12})&(?:amp;)?bitrateid=([0-9]{1,10})&(?:amp;)?vendorid=([0-9]{1,10})&(?:amp;)?type=\.flv',
    'embed-src' => 'http://www.imdb.com/images/js/app/video/mediaplayer.swf?file=http%3A%2F%2Fwww.totaleclips.com%2FPlayer%2FBounce.aspx%3Feclipid%3D$2%26bitrateid%3D$3%26vendorid%3D$4%26type%3D.flv&backcolor=0x000000&frontcolor=0xCCCCCC&lightcolor=0xFFFFCC&shuffle=false&autostart=false',
    'embed-width' => '480',
    'embed-height' => '380',
  ),
  array(
    'title' => 'ImageShack',
    'website' => 'http://www.imageshack.us',
    'url-match' => 'http://img([0-9]{1,5})\.imageshack\.us/img[0-9]{1,5}/[0-9]{1,7}/([a-z0-9-_]{1,28})\.(?:flv|swf)',
    'embed-src' => 'http://img$2.imageshack.us/flvplayer.swf?f=T$3&autostart=false',
    'embed-width' => '424',
    'embed-height' => '338',
  ),
  array(
    'title' => 'IndyaRocks',
    'website' => 'http://www.indyarocks.com',
    'url-match' => 'http://(?:www\.)?indyarocks\.com/videos/(?:(?:(?:(?:[^-"]*?)-){1,10})|embed-)([0-9]{1,8})',
    'embed-src' => 'http://www.indyarocks.com/videos/embed-$2',
    'embed-width' => '425',
    'embed-height' => '350',
  ),
  array(
    'title' => 'iReport',
    'website' => 'http://www.ireport.com',
    'url-match' => 'http://www\.ireport\.com/docs/DOC-([0-9]{4,8})',
    'embed-src' => 'http://www.ireport.com/themes/custom/resources/cvplayer/ireport_embed.swf?player=embed&configPath=http://www.ireport.com&playlistId=$2&contentId=$2/0&',
    'embed-width' => '400',
    'embed-height' => '300',
  ),
  array(
    'title' => 'Izlesene',
    'website' => 'http://www.izlesene.com',
    'url-match' => 'http://(?:www\.)?izlesene\.com/(?:player2\.swf\?video=|video/(?:[a-z0-9-_]*?)/)([0-9]{1,10})',
    'embed-src' => 'http://www.izlesene.com/player2.swf?video=$2',
    'embed-width' => '425',
    'embed-height' => '355',
  ),
  array(
    'title' => 'Jamendo',
    'website' => 'http://www.jamendo.com',
    'url-match' => 'http://(?:www\.|widgets\.)?jamendo\.com/(?:[a-z0-9]*?)/album/(?:\?album_id=)?([0-9]{1,10})',
    'embed-src' => 'http://widgets.jamendo.com/en/album/?album_id=$2&playertype=2008',
    'embed-width' => '200',
    'embed-height' => '300',
  ),
  array(
    'title' => 'Jokeroo',
    'website' => 'http://www.jokeroo.com',
    'url-match' => 'http://(?:www\.)?jokeroo\.com/(auto|educational|financial|health|howto|lawyers|politics|travel|extremesports|funnyvideos)/((?:(?:[0-9a-z]*?)/){0,3})?([0-9a-z_]*?)\.htm',
    'embed-src' => 'http://www.jokeroo.com/promotional_player2.swf?channel&vid=http://uploads.filecabin.com/flash/$4.flv&vid_url=http://www.jokeroo.com/$2/$3$4.html&adv_url',
    'embed-width' => '490',
    'embed-height' => '425',
  ),
  array(
    'title' => 'JujuNation Video',
    'website' => 'http://www.jujunation.com',
    'url-match' => 'http://(?:www\.)?jujunation.com/viewVideo\.php\?video_id=([0-9]{1,10})',
    'embed-src' => 'http://www.jujunation.com/flvplayer.swf?config=http://www.jujunation.com/videoConfigXmlCode.php?pg=video_$2_no_0',
    'embed-width' => '450',
    'embed-height' => '370',
  ),
  array(
    'title' => 'JujuNation Audio',
    'website' => 'http://www.jujunation.com',
    'url-match' => 'http://(?:www\.)?jujunation.com/music\.php\?music_id=([0-9]{1,10})',
    'embed-src' => 'http://www.jujunation.com/player.swf?configXmlPath=http://www.jujunation.com/musicConfigXmlCode.php?pg=music_$2&playListXmlPath=http://www.jujunation.com/musicPlaylistXmlCode.php?pg=music_$2',
    'embed-width' => '220',
    'embed-height' => '66',
  ),
  array(
    'title' => 'JustinTV',
    'website' => 'http://www.justin.tv',
    'url-match' => 'http://(?:\w{0,3}\.)?justin\.tv/(\w*)',
    'embed-src' => 'http://www.justin.tv/widgets/jtv_player.swf?channel=$2&auto_play=false',
    'embed-width' => '353',
    'embed-height' => '295',
  ),
  array(
    'title' => 'Kewego',
    'website' => 'http://www.kewego.co.uk',
    'url-match' => 'http://(?:www\.)?kewego(?:\.co\.uk|\.com)/video/([0-9a-z]*)\.html',
    'embed-src' => 'http://www.kewego.com/swf/p3/epix.swf',
    'embed-width' => '400',
    'embed-height' => '300',
    'flashvars' => 'language_code=en&playerKey=$2&skinKey=71703ed5cea1&sig=iLyROoaftv7I&autostart=false'
  ),
  array(
    'title' => 'Koreus',
    'website' => 'http://www.koreus.com',
    'url-match' => 'http://(?:www\.)?koreus\.com/video/([0-9a-z-]{1,50})(?:\.html)?',
    'embed-src' => 'http://www.koreus.com/video/$2',
    'embed-width' => '400',
    'embed-height' => '320',
  ),
  array(
    'title' => 'Last.fm (Audio)',
    'website' => 'http://www.last.fm',
    'url-match' => 'http://(?:www\.)?last\.fm/music/([0-9a-z%\+_-]*?)/_/([0-9\+a-z_-]*)',
    'embed-src' => 'http://cdn.last.fm/webclient/s12n/s/53/lfmPlayer.swf',
    'embed-width' => '300',
    'embed-height' => '221',
    'flashvars' => 'lang=en&amp;lfmMode=playlist&amp;FOD=true&amp;resname=$3&amp;restype=track&amp;artist=$2',
  ),
  array(
    'title' => 'Last.fm (Video)',
    'website' => 'http://www.last.fm',
    'url-match' => 'http://(?:www\.)?last\.fm/music/([0-9a-zA-Z%\+_-]*?)/\+videos/([0-9\+a-z_-]{2,20})',
    'embed-src' => 'http://cdn.last.fm/videoplayer/l/17/VideoPlayer.swf',
    'embed-width' => '340',
    'embed-height' => '289',
    'flashvars' => 'uniqueName=$3&amp;FSSupport=true&amp;'
  ),
  array(
    'title' => 'Libero',
    'website' => 'http://www.libero.it',
    'url-match' => 'http://video\.libero\.it/app/play(?:/index.html)?\?(?:[^"]*?)?id=([a-f0-9]{32})',
    'embed-src' => 'http://video.libero.it/static/swf/eltvplayer.swf?id=$2.flv&ap=0',
    'embed-width' => '400',
    'embed-height' => '333',
  ),
  array(
    'title' => 'LiveLeak',
    'website' => 'http://www.liveleak.com',
    'url-match' => 'http://(?:www\.)?liveleak\.com/(?:player.swf?autostart=false&(?:amp;)?token=|view\?(?:[^"]*?)?i=|e/)((?:[0-9a-z]{3})_(?:[a-z0-9]{10}))',
    'embed-src' => 'http://www.liveleak.com/e/$2',
    'embed-width' => '450',
    'embed-height' => '370',
  ),
  array(
    'title' => 'LiveVideo',
    'website' => 'http://www.livevideo.com',
    'url-match' => 'http://(?:www\.)?livevideo\.com/(?:flvplayer/embed/|video/(?:view/)?(?:(?:[^"]*?)?/)?)([0-9a-f]{32})',
    'embed-src' => 'http://www.livevideo.com/flvplayer/embed/$2',
    'embed-width' => '445',
    'embed-height' => '369',
  ),
  array(
    'title' => 'Machinima (Old)',
    'website' => 'http://www.machinima.com',
    'url-match' => 'http://(?:www\.)?machinima\.com/(?:film/view&(?:amp;)?id=|#details_)([0-9]{1,8})(?:_contents)?',
    'embed-src' => 'http://www.machinima.com/_flash_media_player/mediaplayer.swf?file=http://machinima.com/p/$2',
    'embed-width' => '400',
    'embed-height' => '300',
  ),
  array(
    'title' => 'Machinima (New)',
    'website' => 'http://www.machinima.com',
    'url-match' => 'http://(?:www\.)?machinima\.com:80/f/([0-9a-f]{32})',
    'embed-src' => 'http://machinima.com:80/_flash_media_player/mediaplayer.swf?file=http://machinima.com:80/f/$2',
    'embed-width' => '400',
    'embed-height' => '300',
  ),
  array(
    'title' => 'MSNBC',
    'website' => 'http://www.msnbc.msn.com/',
    'url-match' => 'http://www\.msnbc\.msn\.com/id/(?:[0-9]{1,9})/vp/([0-9]{1,9})',
    'embed-src' => 'http://msnbcmedia.msn.com/i/MSNBC/Components/Video/_Player/swfs/embedPlayer/EmbeddedPlayer_I4.swf?domain=www.msnbc.msn.com&amp;settings=22425448&amp;useProxy=true&amp;wbDomain=www.msnbc.msn.com&amp;launch=$2&amp;sw=1920&amp;sh=1200&amp;EID=oVPEFC&amp;playerid=22425001',
    'embed-width' => '425',
    'embed-height' => '339',
  ),
  array(
    'title' => 'Video.mail.ru',
    'website' => 'http://video.mail.ru',
    'url-match' => 'http://video\.mail\.ru/mail/([0-9a-z_-]*?)/([0-9]{1,4})/([0-9]{1,4})\.html',
    'embed-src' => 'http://img.mail.ru/r/video2/player_v2.swf?par=http://content.video.mail.ru/mail/$2/$3/\$$4&page=1&username=$2&albumid=$3&id=$4',
    'embed-width' => '452',
    'embed-height' => '385',
  ),
  array(
    'title' => 'MadnessVideo',
    'website' => 'http://www.madnessvideo.net',
    'url-match' => 'http://(?:www\.)?madnessvideo\.net/(.*)',
    'embed-src' => 'http://www.madnessvideo.net/emb.aspx/$2',
    'embed-width' => '400',
    'embed-height' => '320',
  ),
  array(
    'title' => 'MotionBox',
    'website' => 'http://www.motionbox.com',
    'url-match' => 'http://(?:www\.)?motionbox\.com/videos/([0-9a-f]{14})',
    'embed-src' => 'http://www.motionbox.com/external/hd_player/type%3Dsd%2Cvideo_uid%3D$2',
    'embed-width' => '416',
    'embed-height' => '312',
  ),
  array(
    'title' => 'Mpora',
    'website' => 'http://video.mpora.com',
    'url-match' => 'http://video\.mpora\.com/watch/(\w{9})',
    'embed-src' => 'http://video.mpora.com/ep/$2/',
    'embed-width' => '425',
    'embed-height' => '350',
  ),
  array(
    'title' => 'Mp3tube',
    'website' => 'http://www.mp3tube.net',
    'url-match' => '(http://(?:www\.)?mp3tube\.net\/play\.swf\?id=(?:[0-9a-f]{32}))',
    'embed-src' => '$2',
    'embed-width' => '260',
    'embed-height' => '60',
  ),
  array(
    'title' => 'MtvU (Usa Only)',
    'website' => 'http://www.mtvu.com',
    'url-match' => 'http://(?:www\.)?mtvu\.com/video/\?id=([0-9]{1,9})(?:[^"]*?)vid=([0-9]{1,9})',
    'embed-src' => 'http://media.mtvu.com/player/embed/AS3/site/',
    'embed-width' => '423',
    'embed-height' => '318',
    'flashvars' => 'CONFIG_URL=http://media.mtvu.com/player/embed/AS3/site/configuration.jhtml%3fid%3D$2%26vid%3D$3%26autoPlay%3Dfalse&amp;allowFullScreen=true'
  ),

  array(
    'title' => 'MP3 Audio',
    'website' => '',
    'url-match' => '(http://[^"\'\`\<\>\@\*\$]*?\.mp3)$',
    'embed-src' => 'http://www.google.com/reader/ui/3523697345-audio-player.swf',
    'embed-width' => '400',
    'embed-height' => '27',
    'flashvars' => 'audioUrl=$2',
    'download-link' => true,
  ),
  array(
    'title' => 'MyNet',
    'website' => 'http://video.eksenim.mynet.com/',
    'url-match' => 'http://video\.eksenim\.mynet\.com/(?:[0-9a-z_-]*?)/(?:[0-9a-z_-]*?)/([0-9]{1,12})/',
    'embed-src' => 'http://video.eksenim.mynet.com/flvplayers/vplayer9.swf?videolist=http://video.eksenim.mynet.com/batch/video_xml_embed.php?video_id=$2',
    'embed-width' => '400',
    'embed-height' => '334',
  ),
  array(
    'title' => 'MyShows.cn/SeeHaha.com',
    'website' => 'http://www.myshows.cn',
    'url-match' => '(http://www\.seehaha\.com/flash/player\.swf\?vidFileName=(?:[0-9]*?)\.flv)',
    'embed-src' => '$2',
    'embed-width' => '425',
    'embed-height' => '350',
  ),
  array(
    'title' => 'MySpaceTv',
    'website' => 'http://www.myspacetv.com',
    'url-match' => 'http://(?:vids\.myspace|myspacetv)\.com/index\.cfm\?(?:[^"]*?)?VideoID=([0-9]{1,10})',
    'embed-src' => 'http://mediaservices.myspace.com/services/media/embed.aspx/m=$2',
    'embed-width' => '425',
    'embed-height' => '360',
  ),
  array(
    'title' => 'MyVideo',
    'website' => 'http://www.myvideo.de',
    'url-match' => 'http://(?:www\.)?myvideo\.(at|be|ch|de|nl)/(?:watch|movie)/([0-9]{1,8})',
    'embed-src' => 'http://www.myvideo.$2/movie/$3',
    'embed-width' => '470',
    'embed-height' => '406',
  ),
  array(
    'title' => 'MyVi',
    'website' => 'http://myvi.ru',
    'url-match' => '(http://(?:www\.)?myvi\.ru/ru/flash/player/(?:[0-9a-z_-]{45}))',
    'embed-src' => '$2',
    'embed-width' => '450',
    'embed-height' => '418',
  ),
  array(
    'title' => 'M Thai',
    'website' => 'http://video.mthai.com',
    'url-match' => 'http://video\.mthai\.com/player\.php\?(?:[^"]*?)?id=([0-9a-z]{14,20})',
    'embed-src' => 'http://video.mthai.com/Flash_player/player.swf?idMovie=$2',
    'embed-width' => '407',
    'embed-height' => '342',
  ),
  array(
    'title' => 'NewGrounds',
    'website' => 'http://www.newgrounds.com',
    'url-match' => '(http://uploads\.ungrounded\.net/(?:[0-9]{1,12})/(?:[0-9]{1,12})_(?:[0-9a-z_-]*?)\.swf)',
    'embed-src' => '$2?autostart=false&autoplay=false',
    'embed-width' => '480',
    'embed-height' => '400',
),
  array(
    'title' => 'NhacCuaTui',
    'website' => 'http://www.nhaccuatui.com',
    'url-match' => 'http://(?:www\.)?nhaccuatui\.com/(?:nghe\?M=|m/)([a-z0-9-_]{10})',
    'embed-src' => 'http://www.nhaccuatui.com/m/$2',
    'embed-width' => '300',
    'embed-height' => '270',
  ),
  array(
    'title' => 'OnSmash',
    'website' => 'http://www.onsmash.com',
    'url-match' => 'http://(?:www\.|videos\.)?onsmash\.com/(?:v|e)/([0-9a-z]{16})',
    'embed-src' => 'http://videos.onsmash.com/e/$2',
    'embed-width' => '448',
    'embed-height' => '374',
  ),
  array(
    'title' => 'Orb',
    'website' => 'http://www.orb.com',
    'url-match' => 'http://mycast\.orb\.com/orb/html/qs\?mediumId=([0-9a-z]{8})&(?:amp;)?l=([0-9a-z_-]{1,20})',
    'embed-src' => 'http://mycast.orb.com/orb/resources/common/videoplayer.swf?file=http%3A%2F%2Fmycast.orb.com%2Forb%2Fxml%2Fstream%3FstreamFormat%3Dswf%26mediumId%3D$2%26l%3D$3&showdigits=true&autostart=false&shuffle=false&showeq=true&showfsbutton=true',
    'embed-width' => '439',
    'embed-height' => '350',
  ),
  array(
    'title' => 'Photobucket',
    'website' => 'http://www.photobucket.com',
    'url-match' => 'http://media\.photobucket\.com\/video\/.*\/videos',
    'fetch-match' => '(http://vid[0-9]{1,3}\.photobucket\.com/albums/[a-z0-9]{2,5}/[a-z0-9\-_]*/videos/[a-z0-9\-_]*\.flv)',
    'embed-src' => 'http://media.photobucket.com/flash/player.swf?file=$2',
    'embed-width' => '448',
    'embed-height' => '361',
  ),
  array(
    'title' => 'PikNikTube',
    'website' => 'http://www.pikniktube.com',
    'url-match' => 'http://(?:www\.)?pikniktube\.com/(?:v/|(?:(?:[^"]*?)\?q=))([0-9a-f]{32})',
    'embed-src' => 'http://www.pikniktube.com/player/videoplayer2.swf?linktarget=_blank&embedded=1&xmlsrc=http://www.pikniktube.com/getxmle.asp?q=$2&a=1&c=0',
    'embed-width' => '340',
    'embed-height' => '320',
  ),
  array(
    'title' => 'Project Playlist',
    'website' => 'http://www.playlist.com',
    'url-match' => 'http://(?:www\.)?playlist\.com/(?:standalone|node)/([0-9]{1,10})',
    'embed-src' => 'http://www.playlist.com/media/mp3player-standalone.swf?playlist_url=http://www.playlist.com/node/$2/playlist/xspf&config=http://www.musiclist.us/mc/config/config_black.xml&mywidth=435',
    'embed-width' => '435',
    'embed-height' => '270',
  ),
  array(
    'title' => 'Putfile',
    'website' => 'http://www.putfile.com',
    'url-match' => 'http://(?:www\.|media\.|feat\.)?putfile\.com/(?:flow/putfile\.swf\?videoFile=|)?([a-z0-9-_]*)(?:\?)?',
    'embed-src' => 'http://feat.putfile.com/flow/putfile.swf?videoFile=$2',
    'embed-width' => '425',
    'embed-height' => '345',
  ),
  array(
    'title' => 'Rambler',
    'website' => 'http://vision.rambler.ru',
    'url-match' => 'http://vision\.rambler\.ru/(?:i/e\.swf\?id=|users/)((?:[0-9a-z_-]*?)/(?:[0-9]*?)/(?:[0-9]*))',
    'embed-src' => 'http://vision.rambler.ru/i/e.swf?id=$2&logo=1',
    'embed-width' => '390',
    'embed-height' => '370',
  ),
  array(
    'title' => 'RawVegas',
    'website' => 'http://www.rawvegas.tv',
    'url-match' => 'http://(?:www\.)?rawvegas\.tv/watch/[a-z\-0-9]*/([0-9a-f]{30})',
    'embed-src' => 'http://www.rawvegas.tv/ext.php?uniqueVidID=$2',
    'embed-width' => '427',
    'embed-height' => '300',
  ),
  array(
    'title' => 'RuTube',
    'website' => 'http://www.rutube.ru',
    'url-match' => 'http://(?:www\.|video\.)?rutube\.ru/(?:tracks/\d+?\.html\?(?:(?:pos|related)=1&(?:amp;)?)?v=)?([0-9a-f]{32})',
    'embed-src' => 'http://video.rutube.ru/$2',
    'embed-width' => '470',
    'embed-height' => '353',
  ),
  array(
    'title' => 'ScreenToaster',
    'website' => 'http://www.screentoaster.com',
    'url-match' => 'http://(?:www\.)?screentoaster\.com/watch/([0-9a-zA-Z]+)',
    'embed-src' => 'http://www.screentoaster.com/swf/STPlayer.swf?video=$2',
    'embed-width' => '425',
    'embed-height' => '344',
    'flashvars' => 'video=$2',
  ),
  array(
    'title' => 'SevenLoad',
    'website' => 'http://www.sevenload.com',
    'url-match' => 'http://((?:en|tr|de|www)\.)?sevenload\.com/(?:videos|videolar)/([0-9a-z]{1,8})',
    'embed-src' => 'http://$2sevenload.com/pl/$3/425x350/swf',
    'embed-width' => '425',
    'embed-height' => '350',
  ),
  array(
    'title' => 'ShareView',
    'website' => 'http://www.shareview.us',
    'url-match' => 'http://(?:www\.)?shareview\.us/video/([0-9]{1,10})/',
    'embed-src' => 'http://www.shareview.us/nvembed.swf?key=$2',
    'embed-width' => '540',
    'embed-height' => '380',
  ),
  array(
    'title' => 'Sharkle',
    'website' => 'http://www.sharkle.com',
    'url-match' => '(http://(?:www\.)?sharkle\.com/externalPlayer/(?:(?:(?:[0-9a-z]{1,25})/){3}))',
    'embed-src' => '$2',
    'embed-width' => '340',
    'embed-height' => '310',
  ),
  array(
    'title' => 'Smotri',
    'website' => 'http://www.smotri.com',
    'url-match' => 'http://(?:www\.)?smotri\.com/video/view/\?id=v([0-9a-f]{10})',
    'embed-src' => 'http://pics.smotri.com/scrubber_custom8.swf?file=$2&bufferTime=3&autoStart=false&str_lang=eng&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color_lightaqua.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml',
    'embed-width' => '400',
    'embed-height' => '330',
  ),
  array(
    'title' => 'Snotr',
    'website' => 'http://www.snotr.com',
    'url-match' => 'http://(?:www\.|videos\.)?snotr\.com/(?:player\.swf\?video=|)?(?:video|embed)/([0-9]{1,8})',
    'embed-src' => 'http://videos.snotr.com/player.swf?video=$2&amp;embedded=true&amp;autoplay=false',
    'embed-width' => '400',
    'embed-height' => '330',
  ),
  array(
    'title' => 'SouthPark Studios',
    'website' => 'http://www.southparkstudios.com',
    'url-match' => 'http://(?:www\.)?southparkstudios\.com/clips/([0-9]{1,10})',
    'embed-src' => 'http://media.mtvnservices.com/mgid:cms:item:southparkstudios.com:$2:',
    'embed-width' => '480',
    'embed-height' => '360',
  ),
  array(
    'title' => 'Space.tv.cctv.com',
    'website' => 'http://space.tv.cctv.com',
    'url-match' => 'http://((?:(?:[a-z0-9]{1,10})\.){0,2})?cctv\.com/act/video\.jsp\?videoId=VIDE([0-9]{16})',
    'embed-src' => 'http://$2cctv.com/playcfg/player_new.swf?id=VIDE$3&site=http://$2cctv.com&method=http',
    'embed-width' => '500',
    'embed-height' => '400',
  ),
  array(
    'title' => 'Spike',
    'website' => 'http://www.spike.com',
    'url-match' => 'http://(?:www\.)?spike\.com/(?:video/(?:[0-9a-z_-]{2,30})?/|efp\?flvbaseclip=)([0-9]{4,12})',
    'embed-src' => 'http://www.spike.com/efp?flvbaseclip=$2&',
    'embed-width' => '448',
    'embed-height' => '365',
  ),
  array(
    'title' => 'Songza',
    'website' => 'http://www.songza.com',
    'url-match' => '(http://(?:www\.)?songza\.com/e/listen\?(?:zName=(?:[0-9a-z_\%-]*?)&(?:amp;)?)?zId=(?:[0-9a-z_-]{16}))',
    'embed-src' => '$2&zAutostart=false&zType=flv',
    'embed-width' => '425',
    'embed-height' => '114',
  ),
  array(
    'title' => 'Streetfire',
    'website' => 'http://www.streetfire.net',
    'url-match' => 'http://(?:www\.|videos\.)?streetfire\.net/video/(?:[0-9a-z\-_]*)\.htm',
    'fetch-match' => '<link rel="video_src" href="([A-z:\/\.0-9-_=?]*)',
    'embed-src' => '$2',
    'embed-width' => '428',
    'embed-height' => '352',
  ),
  array(
    'title' => 'StupidVideos',
    'website' => 'http://www.stupidvideos.com',
    'url-match' => 'http://(?:www\.|images\.)?stupidvideos\.com/(?:video/(?:[^"\#]*?)\#|images/player/player\.swf\?sa=1&(?:amp;)?sk=7&(?:amp;)?si=2&(?:amp;)?i=)([0-9]{1,10})',
    'embed-src' => 'http://images.stupidvideos.com/2.0.2/swf/video.swf?sa=1&sk=7&si=2&i=$2',
    'embed-width' => '451',
    'embed-height' => '433',
  ),
  array(
    'title' => 'TagTélé',
    'website' => 'http://www.tagtele.com',
    'url-match' => 'http://www\.tagtele\.com/(?:v/|videos/voir/)([0-9]{1,12})',
    'embed-src' => 'http://www.tagtele.com/v/$2',
    'embed-width' => '425',
    'embed-height' => '350',
  ),
  array(
    'title' => 'Ted.com',
    'website' => 'http://www.ted.com',
    'url-match' => 'http://(?:www\.)?ted\.com/(index.php/)?talks/[a-z0-9\-_]*.html',
    'fetch-match' => 'hs:"talks\/dynamic\/([a-z0-9-_]*)-high\.flv',
    'embed-src' => 'http://video.ted.com/assets/player/swf/EmbedPlayer.swf',
    'embed-width' => '446',
    'embed-height' => '326',
    'flashvars' => 'vu=http://video.ted.com/talks/dynamic/$2-medium.flv&su=http://images.ted.com/images/ted/tedindex/embed-posters/$2.embed_thumbnail.jpg&vw=432&vh=240',
  ),
  array(
    'title' => 'The Onion',
    'website' => 'http://www.theonion.com',
    'url-match' => 'http://(?:www\.)?theonion\.com/content/video/.*',
    'fetch-match' => 'videoid\s?=\s?"([0-9]{2,7})";.*var image_url\s?=\s?escape\("([^"]*)"',
    'embed-src' => 'http://www.theonion.com/content/themes/common/assets/onn_embed/embedded_player.swf?image=$3&amp;videoid=$2',
    'embed-width' => '480',
    'embed-height' => '430',
  ),
  array(
    'title' => 'TinyPic',
    'website' => 'http://www.tinypic.com',
    'url-match' => 'http://(?:www\.)?tinypic\.com/player\.php\?v=([0-9a-z-&=]{1,12})',
    'embed-src' => 'http://v5.tinypic.com/player.swf?file=$2',
    'embed-width' => '440',
    'embed-height' => '420',
  ),
  array(
    'title' => 'Todays Big Thing',
    'website' => 'http://www.todaysbigthing.com',
    'url-match' => 'http://(?:www|entertainment|sports|technology|music|funnyvideos)\.todaysbigthing\.com/[0-9]{4}(?:/[0-9]{2}){2}',
    'fetch-match' => 'http://(?:www|entertainment|sports|technology|music|funnyvideos)\.todaysbigthing\.com/betamax/betamax\.internal\.swf\?item_id=([0-9]{1,6})',
    'embed-src' => 'http://www.todaysbigthing.com/betamax/betamax.swf?item_id=$2&fullscreen=1',
    'embed-width' => '480',
    'embed-height' => '360',
  ),
  array(
    'title' => 'TrailerAddict',
    'website' => 'http://www.traileraddict.com',
    'url-match' => 'http://(?:www\.)?traileraddict\.com/trailer/',
    'fetch-match' => '(http://(?:www\.)?traileraddict\.com/em(?:d|b)/(?:[0-9]{1,10}))',
    'embed-src' => '$2',
    'embed-width' => '450',
    'embed-height' => '279',
  ),
  array(
    'title' => 'TrTube',
    'website' => 'http://www.trtube.com',
    'url-match' => 'http://(?:www\.)?trtube\.com/izle\.php\?v=([a-z]{1,12})',
    'embed-src' => 'http://www.trtube.com/mediaplayer_3_15.swf?file=http://www.trtube.com/vid2/89457.flv&image=http://www.trimg.com/vi/89457.gif&autostart=false',
    'embed-width' => '425',
    'embed-height' => '350',
  ),
  array(
    'title' => 'Trilulilu',
    'website' => 'http://www.trilulilu.ro',
    'url-match' => 'http://(?:www\.)?trilulilu\.ro/([0-9a-z_-]*?)/([0-9a-f]{14})',
    'fetch-match' => '<link rel="video_src" href="([A-z:\/\.0-9-_=?]*)\?autoplay', 
    'embed-src' => '$2',
    'embed-width' => '440',
    'embed-height' => '362',
  ),
  array(
    'title' => 'Tu',
    'website' => 'http://www.tu.tv',
    'title' => 'Tu.tv',
    'url-match' => '(http://tu\.tv/tutvweb\.swf\?xtp=(?:[0-9]{1,10}))',
    'embed-src' => '$2',
    'embed-width' => '425',
    'embed-height' => '350',
  ),
  array(
    'title' => 'Tudou',
    'website' => 'http://www.tudou.com',
    'url-match' => 'http://(?:www\.)?tudou\.com/(?:programs/view/|v/)([a-z0-9-]{1,12})',
    'embed-src' => 'http://www.tudou.com/v/$2',
    'embed-width' => '400',
    'embed-height' => '300',
  ),
  array(
    'title' => 'Tumblr (Music)',
    'website' => 'http://www.tumblr.com',
    'url-match' => 'http://[a-z0-9-_]{2,30}\.tumblr\.com/post/[0-9]{3,10}/',
    'fetch-match' => '<embed type="application/x-shockwave-flash" src="(http://[a-z0-9-_./]*\?audio_file=http://www\.tumblr\.com/audio_file/[0-9]{5,8}/[a-z0-9]{24})',
    'embed-src' => '$2&amp;color=e4e4e4',
    'embed-width' => '207',
    'embed-height' => '27',
  ),
  
  array(
    'title' => 'Twitvid',
    'website' => 'http://www.twitvid.com/',
    'url-match' => 'http://(?:www\.)?twitvid\.com/([0-9a-z]{1,10})',
    'embed-src' => 'http://www.twitvid.com/player/$2',
    'embed-width' => '425',
    'embed-height' => '344',
  ),
  array(
    'title' => 'UOL VideoLog',
    'website' => 'http://videolog.uol.com.br',
    'url-match' => 'http://videolog\.uol\.com\.br/video(?:\?|\.php\?id=)([0-9]{1,9})',
    'embed-src' => 'http://www.videolog.tv/swfs/externo_api.swf?v=$2&amp;id_video=$2',
    'embed-width' => '512',
    'embed-height' => '384',
  ),
  array(
    'title' => 'u-Tube',
    'website' => 'http://www.u-tube.ru',
    'url-match' => 'http://(?:www\.)?u-tube\.ru/(?:playlist\.php\?id=|pages/video/)([0-9]{1,12})',
    'embed-src' => 'http://www.u-tube.ru/upload/others/flvplayer.swf?file=http://www.u-tube.ru/playlist.php?id=$2&width=400&height=300',
    'embed-width' => '400',
    'embed-height' => '300',
  ),
  array(
    'title' => 'VideoJug',
    'website' => 'http://www.videojug.com',
    'url-match' => 'http://(?:www\.)videojug\.com/film/',
    'fetch-match' => 'http:\/\/www.videojug.com\/player\/videoJugPlayer.swf\?id=((?:[0-9a-f]{1,12}-?){5})',
    'embed-src' => 'http://www.videojug.com/views/player/Player.swf',
    'embed-width' => '400',
    'embed-height' => '345',
    'flashvars' => 'embedded=true&amp;ClientType=0&amp;id=$2&amp;type=film&amp;host=http%3a%2f%2fwww.videojug.com&amp;ar=16_9',
  ),
  array(
    'title' => 'videos.sapo',
    'website' => 'http://videos.sapo.pt',
    'url-match' => 'http://(www\.|(?:(?:(?:[0-9a-z]{3,12})\.){1,2}))?sapo\.pt/([0-9a-z]{20})',
    'embed-src' => 'http://$2sapo.pt/play?file=http://$2sapo.pt/$3/mov/1',
    'embed-width' => '400',
    'embed-height' => '322',
  ),
  array(
    'title' => 'Vidiac',
    'website' => 'http://www.vidiac.com',
    'url-match' => 'http://(?:www\.)?vidiac\.com/video/((?:[0-9a-z]{8})(?:(?:-(?:[0-9a-z]{4})){3})-(?:[0-9a-z]{12}))\.htm',
    'embed-src' => 'http://www.vidiac.com/vidiac.swf?video=$2',
    'embed-width' => '428',
    'embed-height' => '352',
  ),
  array(
    'title' => 'Viddler',
    'website' => 'http://www.viddler.com',
    'url-match' => '(http://www\.viddler\.com/(?:player|simple)/(?:[0-9a-f]{8})/)',
    'embed-src' => '$2',
    'embed-width' => '437',
    'embed-height' => '288',
  ),
  array(
    'title' => 'Videa',
    'website' => 'http://www.videa.hu',
    'url-match' => 'http://(?:www\.)?videa\.hu/(?:(?:[^"]*)-|flvplayer\.swf\?v=)([0-9a-z]{16})',
    'embed-src' => 'http://videa.hu/flvplayer.swf?v=$2',
    'embed-width' => '434',
    'embed-height' => '357',
  ),
  array(
    'title' => 'VidiLife',
    'website' => 'http://www.vidilife.com',
    'url-match' => '(http://(?:www\.)?vidilife\.com/flash/flvplayer\.swf\?xml=http://(?:www\.)?vidilife\.com/media/play_flash_xml\.cfm\?id=(?:[0-9a-f]{8})-(?:[0-9a-f]{4})-(?:[0-9a-f]{4})-(?:[0-9a-f]{4})-(?:[0-9a-f]{1}))',
    'embed-src' => '$2',
    'embed-width' => '445',
    'embed-height' => '363',
  ),
  array(
    'title' => 'VidMax',
    'website' => 'http://www.vidmax.com',
    'url-match' => 'http://(www\.)?vidmax\.com/(?:index\.php/)?videos?/(?:view/)?([0-9]{1,10})',
    'embed-src' => 'http://www.vidmax.com/player.swf',
    'embed-width' => '400',
    'embed-height' => '300',
    'flashvars' => 'file=http://www.vidmax.com/media/video/$3.flv&amp;streamer=lighttpd&amp;autostart=false&amp;stretching=fill'
  ),
  array(
    'title' => 'Vidivodo',
    'website' => 'http://www.vidivodo.com',
    'url-match' => 'http://www\.vidivodo\.com/VideoPlayerShare\.swf\?lang=([0-9a-z]*?)&(?:amp;)?vidID=([0-9]*?)&(?:amp;)?vCode=v([0-9]*?)&(?:amp;)?dura=([0-9]*?)&(?:amp;)?File=(?:http://video(?:[0-9]*?)\.vidivodo\.com/)?(vidservers/server(?:[0-9]*?)/videos/(?:[0-9]{4})/(?:[0-9]{2})/(?:[0-9]{2})/(?:[0-9]*?)/v(?:[0-9]*?)\.flv)',
    'embed-src' => 'http://www.vidivodo.com/VideoPlayerShare.swf?lang=$2&vidID=$3&vCode=v$4&dura=$5&File=$6',
    'embed-width' => '425',
    'embed-height' => '343',
  ),
  array(
    'title' => 'VoiceThread',
    'website' => 'http://www.voicethread.com',
    'url-match' => 'http://(?:www\.)?voicethread\.com/(?:share/|book\.swf\?b=|#q\.b)([0-9]{1,10})',
    'embed-src' => 'http://www.voicethread.com/book.swf?b=$2',
    'embed-width' => '480',
    'embed-height' => '360',
  ),
  array(
    'title' => 'VSocial (Type1)',
    'website' => 'http://www.vsocial.com/vsandbox/',
    'url-match' => 'http://(?:www\.|static\.)?vsocial\.com/(?:video/|flash/ups\.swf)\?d=([0-9]{1,8})',
    'embed-src' => 'http://static.vsocial.com/flash/ups.swf?d=$2&a=0',
    'embed-width' => '410',
    'embed-height' => '400',
  ),
  array(
    'title' => 'VSocial (Type2)',
    'website' => 'http://www.vsocial.com/vsandbox/',
    'url-match' => '(http://(?:www\.)?vsocial\.com/ups/(?:[a-f0-9]{32}))',
    'embed-src' => '$2',
    'embed-width' => '410',
    'embed-height' => '400',
  ),
  array(
    'title' => 'WeGame',
    'website' => 'http://www.wegame.com',
    'url-match' => 'http://(?:www\.)?wegame\.com/watch/([0-9a-z_-]*?)/',
    'embed-src' => 'http://wegame.com/static/flash/player2.swf?tag=$2',
    'embed-width' => '480',
    'embed-height' => '387',
  ),
  array(
    'title' => 'Webshots (Slideshows)',
    'website' => 'http://www.webshots.com',
    'url-match' => 'http://[a-z0-9\-_]*\.webshots\.com/slideshow/([a-z0-9]*)',
    'embed-src' => 'http://p.webshots.com/flash/smallslideshow.swf',
    'embed-width' => '425',
    'embed-height' => '384',
    'flashvars' => 'playList=http%3A%2F%2Fcommunity.webshots.com%2Fslideshow%2Fmeta%2F$2%3Finline%3Dtrue&inlineUrl=http%3A%2F%2Fcommunity.webshots.com%2FinlinePhoto%26src%3Ds%26referPage%3Dhttp%3A%2F%2Fgood-times.webshots.com%2Fslideshow%2F$2&postRollContent=http%3A%2F%2Fp.webshots.com%2Fflash%2Fws_postroll.swf&shareUrl=http%3A%2F%2Fgood-times.webshots.com%2Fslideshow%2F$2&audio=on&audioVolume=33&autoPlay=false&transitionSpeed=5&startIndex=0&panzoom=on&deployed=true',
  ),
  array(
    'title' => 'Yahoo Video',
    'website' => 'http://video.yahoo.com',
    'url-match' => 'http://(?:(?:www|uk|fr|it|es|br|au|mx|de|ca)\.)?video\.yahoo\.com/watch/([0-9]{1,12})/([0-9]{1,12})',
    'embed-src' => 'http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.1.15',
    'embed-width' => '512',
    'embed-height' => '322',
    'flashvars' => 'id=$3&vid=$2&lang=en-us&intl=us&embed=1'
  ),
  array(
    'title' => 'Yahoo Video HK',
    'website' => 'http://hk.video.yahoo.com',
    'url-match' => 'http://(?:w\.video\.)?hk\.video\.yahoo\.(?:com|net)/video/(?:dplayer\.html\?vid=|video\.html\?id=)([0-9]{1,10})',
    'embed-src' => 'http://w.video.hk.yahoo.net/video/dplayer.html?vid=$2',
    'embed-width' => '420',
    'embed-height' => '370',
  ),
  array(
    'title' => 'Yahoo Music Videos',
    'website' => 'http://music.yahoo.com',
    'url-match' => 'http://(?:new\.)?(?:(?:uk|fr|it|es|br|au|mx|de|ca)\.)?music\.yahoo\.com/[^0-9]*([0-9]{1,12})$',
    'embed-src' => 'http://d.yimg.com/cosmos.bcst.yahoo.com/up/fop/embedflv/swf/fop.swf?id=v$2&eID=0000000&lang=us&enableFullScreen=0&shareEnable=1',
    'embed-width' => '400',
    'embed-height' => '255',
  ),
  array(
    'title' => 'YouKu',
    'website' => 'http://www.youku.com',
    'url-match' => 'http://(?:v\.youku\.com/v_show/id_|player\.youku\.com/player\.php/sid/)([0-9a-z]{6,14})',
    'embed-src' => 'http://player.youku.com/player.php/sid/$2=/v.swf',
    'embed-width' => '450',
    'embed-height' => '372',
  ),
  array(
    'title' => 'You.Video.Sina.com.cn',
    'website' => 'http://you.video.sina.com.cn',
    'url-match' => 'http://(?:vhead\.blog|you\.video)\.sina\.com\.cn/(?:player/(?:[^"]*?)vid=|b/)([0-9]{5,12})(?:-|&(?:amp;)?uid=)([0-9]{5,12})',
    'embed-src' => 'http://vhead.blog.sina.com.cn/player/outer_player.swf?auto=0&vid=$2&uid=$3',
    'embed-width' => '480',
    'embed-height' => '370',
  ),
  array(
    'title' => 'Local Content',
    'website' => 'localhost',
    'url-match' => '__local__(.*)',
    'embed-src' => '$2',
    'embed-width' => '425',
    'embed-height' => '344',
  ),
);