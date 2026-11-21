<?php
namespace Modules\AiReception\ProvidersApi;
/** Only a verified gateway response reporting an explicit provider HTTP 429 may retry. */
final class RetryableDelivery extends \RuntimeException {}