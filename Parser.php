<?php

class Parser {

    protected $sections = [
        'title',
        'location',
        'rate',
        'duration',
        'date',
        'visa',
        'skill',
        'description',
        'responsib',
        'certification',
        'interview',
        'drug'
    ];
    protected $text;
    protected $parts;

    public function __construct(string $text)
    {
        $this->text = $text;

        $this->parse();
    }

    public function parse()
    {
        $sectionParts = array_combine($this->sections, array_fill(0, count($this->sections), ''));

        $currentKey = false;


        $lines = array_filter(explode(PHP_EOL, $this->text));
        foreach ($lines as $line) {
            $key = $this->detectSection($line);

            if (!empty($key)) {
                $currentKey = $key;
                $sectionParts[$currentKey] .= PHP_EOL . $line;
            } elseif (!empty($currentKey)) {
                $sectionParts[$currentKey] .= PHP_EOL . $line;
            }
        }

        $this->parts = $sectionParts;

        return $this;
    }

    protected function detectSection($line)
    {
        foreach ($this->sections as $key) {
            if (preg_match('#\b' . $key . '#i', $line) === 1) {
                return $key;
            }
        }
        return false;
    }

    public function getParts()
    {
        return $this->parts;
    }

    public function getJobPost()
    {
        $jobPost = strstr($this->text, 'Job Details');

        if (empty($jobPost)) {
            $jobPost = strstr($this->text, 'Skills');
        }

        return $jobPost;
    }

    public function filterMainContent($contentText)
    {
        if (strpos($contentText, '*') !== false) {
            return trim(trim(strstr($contentText, '*'), '*'));
        } elseif (strpos($contentText, ':') !== false) {
            return trim(trim(strstr($contentText, ':'), ':'));
        }

        return trim($contentText);
    }

    public function getTitle()
    {
        preg_match('#\btitle([^\r\n]*)#i', $this->text, $titles);
        $title = trim($titles[1] ?? '');



        return trim($this->filterMainContent($title));
    }

    public function getLocation()
    {
        preg_match('#\blocation([^\r\n]*)#i', $this->text, $location);
        $location = $this->filterMainContent(trim($location[1] ?? ''));

        if (empty($location)) {
            $location = $this->parts['location'] ?? '';
        }

        $location = $this->filterMainContent($location);

        preg_match('#([\d]+)#', $location, $zip);
        $zip = str_pad($zip[1] ?? 0, 5, '0', STR_PAD_LEFT);

        return compact('location', 'zip');
    }

    public function getRate()
    {
        preg_match('#\brate([^\r\n]*)#i', $this->text, $rate);
        $rate = trim($this->filterMainContent($rate[1] ?? ''));

        if (empty($rate)) {
            $rate = $this->parts['rate'] ?? '';
        }

        return (int) trim($this->filterMainContent($rate));
    }

    public function getDuration()
    {
        preg_match('#\bduration([^\r\n]*)#i', $this->text, $durationTxt);

        $durationTxt = $durationTxt[1] ?? '';

        preg_match('#([\d]+)#', $durationTxt, $duration);
        $duration = $duration[1] ?? 0;

        $type = 1;
        if (stripos($durationTxt, 'month') !== false) {
            $type = 2;
        }

        return ['duration' => $duration, 'type' => $type];
    }

    public function getDate()
    {
        preg_match('#\bdate([^\r\n]*)#i', $this->text, $date);
        $date = trim($this->filterMainContent($date[1] ?? ''));

        if (empty($date)) {
            $date = $this->parts['date'] ?? '';
        }

        return trim($this->filterMainContent($date));
    }

    public function getVisa()
    {
        if (!empty($this->parts['visa'])) {
            if (stripos($this->parts['visa'], 'no') !== false) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function getExperience()
    {
        preg_match('#years of experience[\s\S]*?([\d]+)#i', $this->text, $experience);

        return (int) trim($experience[1] ?? '');
    }

    public function getSkill()
    {
        $skills = $this->parts['skill'] ?? '';

        $skillParts = explode('Nice to have skill', $skills);

        $primary = $this->prepareSkills($skillParts[0] ?? '');
        $secondary = $this->prepareSkills('Nice to have skill' . ($skillParts[1] ?? ''));

        return compact('primary', 'secondary');
    }

    protected function prepareSkills(string $skills)
    {
        $lines = explode(PHP_EOL, $skills);
        $preparedSkills = [];

        foreach ($lines as $line) {
            if (stripos($line, 'skill') === false) {
                $preparedSkills[] = trim(preg_replace('#[\d]+\.#', '', $line));
            }
        }

        return array_filter($preparedSkills);
    }

    public function getDescription()
    {
        return $this->parts['description'] ?? '';
    }

    public function getResponsibility()
    {
        return $this->parts['responsib'] ?? '';
    }

    public function getCertification()
    {
        if (!empty($this->parts['certification'])) {
            if (stripos($this->parts['certification'], 'no') !== false) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function getInterview()
    {
        if (!empty($this->parts['interview'])) {
            if (stripos($this->parts['interview'], 'no') !== false) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function getDrugTest()
    {
        if (!empty($this->parts['drug'])) {
            if (stripos($this->parts['drug'], 'no') !== false) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function returnRequirements()
    {
        return [
            'title' => $this->getTitle(),
            'location' => $this->getLocation(),
            'rate' => $this->getRate(),
            'duration' => $this->getDuration(),
            'date' => $this->getDate(),
            'visa' => $this->getVisa(),
            'skill' => $this->getSkill(),
            'experience' => $this->getExperience(),
            'description' => $this->getDescription(),
            'responsibility' => $this->getResponsibility(),
            'certification' => $this->getCertification(),
            'interview' => $this->getInterview(),
            'drugTest' => $this->getDrugTest()
        ];
    }

}
