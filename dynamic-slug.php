<?php
/**
 * @source: Codeigniter 3 Helper url_title()
 */
function neon_url_title($str, $separator = '-', $lowercase = FALSE)
	{
		if ($separator === 'dash') {
			$separator = '-';
		} elseif ($separator === 'underscore') {
			$separator = '_';
		}

		$q_separator = preg_quote($separator, '#');

		$trans = array(
			'&.+?;'			=> '',
			'[^\w\d _-]'		=> '',
			'\s+'			=> $separator,
			'('.$q_separator.')+'	=> $separator
		);

		$str = strip_tags($str);
		foreach ($trans as $key => $val) {
			$str = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $str);
		}

		if ($lowercase === TRUE) {
			$str = strtolower($str);
		}

		return trim(trim($str, $separator));
}

class Sample_Slug {
  private function slug_exist() {
        return [
            'header-redirect-in-two-flavors',
            'header-redirect-in-two-flavors-1',
            'header-redirect-in-two-flavors-2'
        ];
    }

    private function slugfy($title, $count = 0) {
        $slug = strtolower(neon_url_title($title));
        if ( $count > 0 ) {
            $slug .= '-' . $count;
        }

        if ( in_array($slug, $this->slug_exist()) ) {
            return $this->slugfy($title, $count + 1);
        }

        return $slug;
    }

    public function slug() {
        echo $this->slugfy("Header redirect in two flavors");
    }
}


