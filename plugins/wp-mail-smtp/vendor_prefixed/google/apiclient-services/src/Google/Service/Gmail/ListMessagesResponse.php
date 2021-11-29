<?php

namespace WPMailSMTP\Vendor;

/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
class Google_Service_Gmail_ListMessagesResponse extends \WPMailSMTP\Vendor\Google_Collection
{
    protected $collection_key = 'messages';
    protected $messagesType = 'Google_Service_Gmail_Message';
    protected $messagesDataType = 'array';
    public $nextPageToken;
    public $resultSizeEstimate;
    /**
     * @param Google_Service_Gmail_Message[]
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }
    /**
     * @return Google_Service_Gmail_Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
    public function setNextPageToken($nextPageToken)
    {
        $this->nextPageToken = $nextPageToken;
    }
    public function getNextPageToken()
    {
        return $this->nextPageToken;
    }
    public function setResultSizeEstimate($resultSizeEstimate)
    {
        $this->resultSizeEstimate = $resultSizeEstimate;
    }
    public function getResultSizeEstimate()
    {
        return $this->resultSizeEstimate;
    }
}
