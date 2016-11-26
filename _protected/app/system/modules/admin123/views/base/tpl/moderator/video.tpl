{if !empty($videos)}

  <ul>

    {each $video in $videos}

      {{ $action = ($video->approved == 1) ? 'disapprovedvideo' : 'approvedvideo' }}

      <div class="m_video">
        {{ VideoDesignCore::generate($video, 'preview', 200, 200) }}
        <p class="italic">{lang 'Posted by'} <a href="{% $oUser->getProfileLink($video->username) %}" target="_blank">{% $video->username %}</a></p>

        <div>
          {{ $text = ($video->approved == 1) ? t('Disapproved') : t('Approved') }}
          {{ LinkCoreForm::display($text, PH7_ADMIN_MOD,'moderator',$action, array('video_id'=>$video->videoId)) }} |
          {{ LinkCoreForm::display(t('Delete'), PH7_ADMIN_MOD, 'moderator', 'deletevideo', array('album_id'=>$video->albumId, 'video_id'=>$video->videoId, 'id'=>$video->profileId, 'username'=>$video->username, 'video_link'=>$video->file)) }}
        </div>
      </div>

    {/each}

  </ul>

  {main_include 'page_nav.inc.tpl'}

{else}

  <p class="center">{lang 'No Videos found for the moderation treatment.'}</p>

{/if}
