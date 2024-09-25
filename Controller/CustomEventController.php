<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Controller;

use Doctrine\DBAL\Driver\IBMDB2\Connection;
use Mautic\CampaignBundle\Controller\EventController;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CampaignBundle\Event\CampaignEvent;
use Mautic\CampaignBundle\EventCollector\EventCollector;
use Mautic\CampaignBundle\Form\Type\EventType;
use Mautic\CampaignBundle\Model\EventModel;
use MauticPlugin\SurgeExtendedCampaignBundle\Form\Type\CustomEventType;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomEventController extends EventController
{
    private $supportedEventTypes = [
        Event::TYPE_DECISION,
        Event::TYPE_ACTION,
        Event::TYPE_CONDITION,
    ];

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $success = 0;
        $valid   = $cancelled   = false;
        $method  = $this->request->getMethod();
        $session = $this->get('session');
        if ('POST' == $method) {
            $event                = $this->request->request->get('campaignevent');
            $type                 = $event['type'];
            $eventType            = $event['eventType'];
            $campaignId           = $event['campaignId'];
            $anchorName           = $event['anchor'];
            $event['triggerDate'] = (!empty($event['triggerDate'])) ? $this->factory->getDate($event['triggerDate'])->getDateTime() : null;
        } else {
            $type       = $this->request->query->get('type');
            $eventType  = $this->request->query->get('eventType');
            $campaignId = $this->request->query->get('campaignId');
            $anchorName = $this->request->query->get('anchor', '');
            $event      = [
                'type'            => $type,
                'eventType'       => $eventType,
                'campaignId'      => $campaignId,
                'anchor'          => $anchorName,
                'anchorEventType' => $this->request->query->get('anchorEventType', ''),
            ];
        }

        //set the eventType key for events
        if (!in_array($eventType, $this->supportedEventTypes)) {
            return $this->modalAccessDenied();
        }

        //ajax only for form fields
        if (!$type
            || !$this->request->isXmlHttpRequest()
            || !$this->get('mautic.security')->isGranted(
                [
                    'campaign:campaigns:edit',
                    'campaign:campaigns:create',
                ],
                'MATCH_ONE'
            )
        ) {
            return $this->modalAccessDenied();
        }

        /** @var EventCollector $eventCollector */
        $eventCollector = $this->get('mautic.campaign.event_collector');

        //fire the builder event
        $events = $eventCollector->getEventsArray();
        $form   = $this->get('form.factory')->create(
            CustomEventType::class,
            $event,
            [
                'action'   => $this->generateUrl('mautic_campaignevent_action', ['objectAction' => 'new']),
                'settings' => $events[$eventType][$type],
            ]
        );
        $event['settings'] = $events[$eventType][$type];

        $form->get('campaignId')->setData($campaignId);

        //Check for a submitted form and process it
        if ('POST' == $method) {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $success = 1;

                    //form is valid so process the data
                    $keyId = 'new'.hash('sha1', uniqid(mt_rand()));

                    //save the properties to session
                    $modifiedEvents = $session->get('mautic.campaign.'.$campaignId.'.events.modified');
                    $formData       = $form->getData();
                    $event          = array_merge($event, $formData);
                    $event['id']    = $event['tempId']    = $keyId;

                    if (empty($event['name'])) {
                        $event['name'] = $this->get('translator')->trans($event['settings']['label']);
                    }
                    if(isset($event['triggerIntervalType'])){
                        $event['properties']['triggerIntervalType'] = $event['triggerIntervalType'];
                    }
                    if(isset($event['triggerIntervalStatus'])){
                      $event['properties']['triggerIntervalStatus'] = $event['triggerIntervalStatus'];
                    }
                    $modifiedEvents[$keyId] = $event;
                    $session->set('mautic.campaign.'.$campaignId.'.events.modified', $modifiedEvents);
                } else {
                    $success = 0;
                }
            }
        }

        $viewParams = ['type' => $type];
        if ($cancelled || $valid) {
            $closeModal = true;
        } else {
            $closeModal = false;
            $formThemes = ['MauticCampaignBundle:FormTheme\Event'];
            if (isset($event['settings']['formTheme'])) {
                $formThemes[] = $event['settings']['formTheme'];
            }

            $viewParams['form']             = $this->setFormTheme($form, 'MauticCampaignBundle:Campaign:index.html.php', $formThemes);
            $viewParams['eventHeader']      = $this->get('translator')->trans($event['settings']['label']);
            $viewParams['eventDescription'] = (!empty($event['settings']['description'])) ? $this->get('translator')->trans(
                $event['settings']['description']
            ) : '';
        }

        $viewParams['hideTriggerMode'] = isset($event['settings']['hideTriggerMode']) && $event['settings']['hideTriggerMode'];

        $passthroughVars = [
            'mauticContent' => 'campaignEvent',
            'success'       => $success,
            'route'         => false,
        ];

        if (!empty($keyId)) {
            //prevent undefined errors
            $entity = new Event();
            $blank  = $entity->convertToArray();
            $event  = array_merge($blank, $event);

            $template = (empty($event['settings']['template'])) ? 'MauticCampaignBundle:Event:generic.html.php'
                : $event['settings']['template'];

            $passthroughVars['event']     = $event;
            $passthroughVars['eventId']   = $keyId;
            $passthroughVars['eventHtml'] = $this->renderView(
                $template,
                [
                    'event'      => $event,
                    'id'         => $keyId,
                    'campaignId' => $campaignId,
                ]
            );
            $passthroughVars['eventType'] = $eventType;

            $translator = $this->translator;
            if ('interval' == $event['triggerMode']) {
                $label = 'mautic.campaign.connection.trigger.interval.label';
                if ('no' == $anchorName) {
                    $label .= '_inaction';
                }
                $passthroughVars['label'] = $translator->trans(
                    $label,
                    [
                        '%number%' => $event['triggerInterval'],
                        '%unit%'   => $translator->trans(
                            'mautic.campaign.event.intervalunit.'.$event['triggerIntervalUnit'],
                            ['%count%' => $event['triggerInterval']]
                        ),
                    ]
                );
            } elseif ('date' == $event['triggerMode']) {
                /** @var \Mautic\CoreBundle\Templating\Helper\DateHelper $dh */
                $dh    = $this->factory->getHelper('template.date');
                $label = 'mautic.campaign.connection.trigger.date.label';
                if ('no' == $anchorName) {
                    $label .= '_inaction';
                }
                $passthroughVars['label'] = $translator->trans(
                    $label,
                    [
                        '%full%' => $dh->toFull($event['triggerDate']),
                        '%time%' => $dh->toTime($event['triggerDate']),
                        '%date%' => $dh->toShort($event['triggerDate']),
                    ]
                );
            }
        }

        if ($closeModal) {
            //just close the modal
            $passthroughVars['closeModal'] = 1;

          return new JsonResponse($passthroughVars);
        } else {
            return $this->ajaxAction(
                [
                    'contentTemplate' => 'SurgeExtendedCampaignBundle:Event:form.html.php',
                    'viewParameters'  => $viewParams,
                    'passthroughVars' => $passthroughVars,
                ]
            );
        }
    }

    /**
     * Generates edit form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId)
    {
        $session       = $this->get('session');
        $valid         = $cancelled = false;
        $method        = $this->request->getMethod();
        $campaignEvent = $this->request->request->get('campaignevent', []);
        $campaignId    = 'POST' === $method
            ? ($campaignEvent['campaignId'] ?? '')
            : $this->request->query->get('campaignId');
        $modifiedEvents = $session->get('mautic.campaign.'.$campaignId.'.events.modified', []);
        $event          = array_key_exists($objectId, $modifiedEvents) ? $modifiedEvents[$objectId] : [];
        if ('POST' === $method) {

            $event = array_merge($event, [
                'anchor'          => $campaignEvent['anchor'] ?? '',
                'anchorEventType' => $campaignEvent['anchorEventType'] ?? '',
            ]);
        } else {
            if (!isset($event['anchor']) && !empty($event['decisionPath'])) {
                // Used to generate label
                $event['anchor'] = $event['decisionPath'];
            }

            if ($this->request->query->has('anchor')) {
                // Override the anchor
                $event['anchor'] = $this->request->get('anchor');
            }

            if ($this->request->query->has('anchorEventType')) {
                // Override the anchorEventType
                $event['anchorEventType'] = $this->request->get('anchorEventType');
            }
            if(!isset($event['triggerIntervalType'])){

                $event['triggerIntervalType'] = $event['properties']['triggerIntervalType'];
            }
            if(!isset($event['triggerIntervalStatus'])){
              $event['triggerIntervalStatus'] = $event['properties']['triggerIntervalStatus'];
          }            
        }

        /*
         * If we don't have an event, don't support the event type, this is not an
         * AJAX request, or we are not granted campaign edit/create, deny access.
         */
        if (empty($event)
            || empty($event['eventType'])
            || !in_array($event['eventType'], $this->supportedEventTypes)
            || !isset($event['type'])
            || !$this->request->isXmlHttpRequest()
            || !$this->get('mautic.security')->isGranted(
                    [
                    'campaign:campaigns:edit',
                    'campaign:campaigns:create',
                ],
                'MATCH_ONE'
            )
        ) {
            return $this->modalAccessDenied();
        }

        /**
         * Fire the CampaignBuilderEvent event to get all events.
         *
         * We can directly dereference the return value here to get
         * the supported events for this type because we already made
         * sure that we're accessing a supported event type above.
         *
         * Method getEventsArray() returns translated labels & descriptions
         */

        /** @var EventCollector $eventCollector */
        $eventCollector  = $this->get('mautic.campaign.event_collector');
        $supportedEvents = $eventCollector->getEventsArray()[$event['eventType']];

        $form = $this->get('form.factory')->create(
            CustomEventType::class,
            $event,
            [
                'action'   => $this->generateUrl('mautic_campaignevent_action', ['objectAction' => 'edit', 'objectId' => $objectId]),
                'settings' => $supportedEvents[$event['type']],
            ]
        );
        $event['settings'] = $supportedEvents[$event['type']];

        $form->get('campaignId')->setData($campaignId);


        // Check for a submitted form and process it
        if ('POST' === $method) {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $formData = $form->getData();
                    $event    = array_merge($event, $formData);
                    if(isset($event['triggerIntervalType'])){
                        $event['properties']['triggerIntervalType'] = $event['triggerIntervalType'];
                    }

                    if(isset($event['triggerIntervalStatus'])){
                      $event['properties']['triggerIntervalStatus'] = $event['triggerIntervalStatus'];
                    }
                    
                    // Set the name to the event default if not known.
                    if (empty($event['name'])) {
                        $event['name'] = $event['settings']['label'];
                    }

                    $modifiedEvents[$objectId] = $event;

                    // Save the modified event properties to session
                    $session->set('mautic.campaign.'.$campaignId.'.events.modified', $modifiedEvents);
                }
            }
        }
      

        $viewParams = [
            'type'            => $event['type'],
            'hideTriggerMode' => isset($event['settings']['hideTriggerMode']) && $event['settings']['hideTriggerMode'],
        ];

        $passthroughVars = [
            'mauticContent' => 'campaignEvent',
            'success'       => !$cancelled && $valid,
            'route'         => false,
        ];

        if (!$cancelled && !$valid) {
            $formThemes = ['MauticCampaignBundle:FormTheme\Event'];

            if (isset($event['settings']['formTheme'])) {
                $formThemes[] = $event['settings']['formTheme'];
            }

            $viewParams = array_merge($viewParams, [
                'form'             => $this->setFormTheme($form, 'MauticCampaignBundle:Campaign:index.html.php', $formThemes),
                'eventHeader'      => $event['settings']['label'],
                'eventDescription' => $event['settings']['description'],
            ]);
            
            return $this->ajaxAction(
                [
                    'contentTemplate' => 'SurgeExtendedCampaignBundle:Event:form.html.php',
                    'viewParameters'  => $viewParams,
                    'passthroughVars' => $passthroughVars,
                ]
            );
        }

        if (!$cancelled && $valid) {
            // Prevent undefined errors
            $event    = array_merge((new Event())->convertToArray(), $event);
            $template = isset($event['settings']['template'])
                ? $event['settings']['template']
                : 'MauticCampaignBundle:Event:generic.html.php';

            $passthroughVars = array_merge($passthroughVars, [
                'event'      => $event,
                'eventId'    => $objectId,
                'eventType'  => $event['eventType'],
                'updateHtml' => $this->renderView(
                    $template,
                    [
                        'event'      => $event,
                        'id'         => $objectId,
                        'update'     => true,
                        'campaignId' => $campaignId,
                    ]
                ),
            ]);

            if (Event::TRIGGER_MODE_INTERVAL === $event['triggerMode']) {
                $label = 'mautic.campaign.connection.trigger.interval.label';

                if (Event::PATH_INACTION === $event['anchor']) {
                    $label .= '_inaction';
                }

                $passthroughVars['label'] = $this->translator->trans(
                    $label,
                    [
                        '%number%' => $event['triggerInterval'],
                        '%unit%'   => $this->translator->trans(
                            'mautic.campaign.event.intervalunit.'.$event['triggerIntervalUnit'],
                            ['%count%' => $event['triggerInterval']]
                        ),
                    ]
                );
            }

            if (Event::TRIGGER_MODE_DATE === $event['triggerMode']) {
                $label = 'mautic.campaign.connection.trigger.date.label';

                if (Event::PATH_INACTION === $event['anchor']) {
                    $label .= '_inaction';
                }

                /** @var \Mautic\CoreBundle\Templating\Helper\DateHelper $dh */
                $dh                       = $this->get('mautic.helper.template.date');
                $passthroughVars['label'] = $this->translator->trans(
                    $label,
                    [
                        '%full%' => $dh->toFull($event['triggerDate']),
                        '%time%' => $dh->toTime($event['triggerDate']),
                        '%date%' => $dh->toShort($event['triggerDate']),
                    ]
                );
            }
        }

        // Just close the modal
        $passthroughVars['closeModal'] = 1;

        return new JsonResponse($passthroughVars);
    }
}
