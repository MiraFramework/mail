<?php

namespace Mira\Mail;

class Mail 
{
    public function __construct($mail_template = null, $template_tags = null)
    {
        if (isset($template_tags)) {
            $this->template_tags = $template_tags;
        }

        if (isset($mail_template)) {
            $this->mail_template = $mail_template;
            $this->template = file_get_contents(__PROJECT__."/application/Providers/Mail/".$this->mail_template.".md");
            $this->template = $this->renderTemplateReplacementTags($this->template);
            $this->subject = $this->getSubject();
        }

        $transport = (new \Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
        ->setUsername(getenv('mailFrom'))
        ->setPassword(getenv('mailPassword'));

        $mailer = new \Swift_Mailer($transport);

        // Create a message
        $message = (new \Swift_Message($this->subject))
        ->setFrom([getenv('mailFrom') => getenv('mailFromName')])
        ->setTo([$this->template_tags['email']])
        ->setBody($this->getTemplate(), 'text/html');

        // Send the message
        $result = $mailer->send($message);
    }

    public function getTemplateText($var1="",$var2="",$pool){
        $temp1 = strpos($pool,$var1)+strlen($var1);
        $result = substr($pool,$temp1,strlen($pool));
        $dd=strpos($result,$var2);
        if($dd == 0){
            $dd = strlen($result);
        }

        return substr($result,0,$dd);
    }

    private function getSubject()
    {
        $this->subject = $this->getTemplateText('@subject', '@endsubject', $this->template);
        $this->template = str_replace('@subject', '', $this->template);
        $this->template = str_replace('@endsubject', '', $this->template);
        $this->template = str_replace($this->subject, '', $this->template);

        return $this->subject;
    }

    private function getTemplate()
    {
        $Parsedown = new \Parsedown();

        return $Parsedown->text($this->template);
    }

    private function renderTemplateReplacementTags($template)
    {
        if (isset($this->template_tags)) {
            foreach($this->template_tags as $key => $value) {
                $template = str_replace('{'.$key.'}', $value, $template);
            }
        }

        return $template;
    }
}