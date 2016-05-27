<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Agreement;
use AppBundle\Entity\Workday;
use AppBundle\Form\Model\Calendar;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AgreementController extends Controller
{
    /**
     * @Route("/acuerdos/{id}/calendario", name="agreement_calendar", methods={"GET"})
     * @Security("is_granted('AGREEMENT_MANAGE', agreement)")
     */
    public function agreementGraphicCalendarAction(Agreement $agreement)
    {
        $totalHours = $agreement->getStudent()->getStudentGroup()->getTraining()->getProgramHours();
        $agreementHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->countHours($agreement);
        $studentHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->countAgreementHours($agreement->getStudent());
        $calendar = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workday')->getArrayCalendar($agreement->getWorkdays());
        return $this->render('calendar/agreement_calendar_graphic.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_agreement'),
            'breadcrumb' => [
                ['fixed' => (string) $agreement, 'path' => 'admin_agreement_form', 'options' => ['id' => $agreement->getId()]],
                ['fixed' => $this->get('translator')->trans('browse.title', [], 'calendar')]
            ],
            'calendar' => $calendar,
            'agreement' => $agreement,
            'total_hours' => $totalHours,
            'agreement_hours' => $agreementHours,
            'student_hours' => $studentHours
        ]);
    }

    /**
     * @Route("/acuerdos/{id}/calendario/incorporar", name="agreement_calendar_add", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_MANAGE', agreement)")
     */
    public function addAgreementCalendarAction(Agreement $agreement, Request $request)
    {
        $totalHours = $agreement->getStudent()->getStudentGroup()->getTraining()->getProgramHours();
        $agreementHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->countHours($agreement);
        $studentHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->countAgreementHours($agreement->getStudent());

        $calendar = new Calendar(max(0, $totalHours - $studentHours));

        $form = $this->createForm('AppBundle\Form\Type\CalendarType', $calendar, [
            'program_hours' => $totalHours
        ]);

        $form->handleRequest($request);

        $workdays = new ArrayCollection();

        if ($form->isValid() && $form->isSubmitted()) {
            $workdays = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workday')->createCalendar($calendar, $agreement);

            if ($request->request->has('submit')) {
                $this->getDoctrine()->getManager()->flush();
                $agreement->setFromDate($this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getRealFromDate($agreement));
                $agreement->setToDate($this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getRealToDate($agreement));
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'calendar'));
                return $this->redirectToRoute('agreement_calendar', ['id' => $agreement->getId()]);
            }
        }

        $calendar = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workday')->getArrayCalendar($workdays);

        return $this->render('calendar/agreement_calendar_add.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_agreement'),
            'breadcrumb' => [
                ['fixed' => (string) $agreement, 'path' => 'admin_agreement_form', 'options' => ['id' => $agreement->getId()]],
                ['fixed' => $this->get('translator')->trans('browse.title', [], 'calendar'), 'path' => 'agreement_calendar', 'options' => ['id' => $agreement->getId()]],
                ['fixed' => $this->get('translator')->trans('form.add', [], 'calendar')]
            ],
            'agreement' => $agreement,
            'total_hours' => $totalHours,
            'agreement_hours' => $agreementHours,
            'student_hours' => $studentHours,
            'form' => $form->createView(),
            'calendar' => $calendar
        ]);
    }

    public function lockWorkdayAction(Agreement $agreement, Request $request, $status)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->request->has('ids')) {
            try {
                $ids = $request->request->get('ids');

                $em->createQuery('UPDATE AppBundle:Workday w SET w.locked = :locked WHERE w.id IN (:ids) AND w.agreement = :agreement')
                        ->setParameter('locked', $status)
                        ->setParameter('ids', $ids)
                        ->setParameter('agreement', $agreement)
                        ->execute();
                
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.locked', [], 'calendar'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.locked_error', [], 'calendar'));
            }
        }
        return $this->redirectToRoute('agreement_calendar', ['id' => $agreement->getId()]);
    }
    
    /**
     * @Route("/acuerdos/{id}/calendario/operacion", name="agreement_calendar_operation", methods={"POST"})
     * @Security("is_granted('AGREEMENT_MANAGE', agreement)")
     */
    public function deleteAgreementCalendarAction(Agreement $agreement, Request $request)
    {
        if ($request->request->has('lock')) {
            return $this->lockWorkdayAction($agreement, $request, true);
        }
        if ($request->request->has('unlock')) {
            return $this->lockWorkdayAction($agreement, $request, false);
        }

        $em = $this->getDoctrine()->getManager();

        if ($request->request->has('ids')) {
            try {
                $ids = $request->request->get('ids');

                $dates = $em->getRepository('AppBundle:Workday')->createQueryBuilder('w')
                    ->where('w.id IN (:ids)')
                    ->andWhere('w.agreement = :agreement')
                    ->setParameter('ids', $ids)
                    ->setParameter('agreement', $agreement)
                    ->getQuery()
                    ->getResult();

                /** @var Workday $date */
                foreach ($dates as $date) {
                    if ($date->getTrackedHours() === 0) {
                        $em->remove($date);
                    }
                }

                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.deleted', [], 'calendar'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], 'calendar'));
            }
        }
        return $this->redirectToRoute('agreement_calendar', ['id' => $agreement->getId()]);
    }

    /**
     * @Route("/jornada/{id}", name="agreement_calendar_form", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_MANAGE', workday.getAgreement())")
     */
    public function agreementCalendarFormAction(Workday $workday, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\WorkdayType', $workday);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'calendar'));
            return $this->redirectToRoute('agreement_calendar', ['id' => $workday->getAgreement()->getId()]);
        }
        return $this->render('calendar/agreement_calendar_form.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_agreement'),
            'breadcrumb' => [
                ['fixed' => (string) $workday->getAgreement(), 'path' => 'admin_agreement_form', 'options' => ['id' => $workday->getAgreement()->getId()]],
                ['fixed' => $this->get('translator')->trans('browse.title', [], 'calendar'), 'path' => 'agreement_calendar', 'options' => ['id' => $workday->getAgreement()->getId()]],
                ['fixed' => $this->get('translator')->trans('form.add', [], 'calendar')]
            ],
            'form' => $form->createView(),
            'workday' => $workday
        ]);
    }
}
