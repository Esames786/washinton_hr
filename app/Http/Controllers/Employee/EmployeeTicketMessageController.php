<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTicket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class EmployeeTicketMessageController extends Controller
{

    public function index(EmployeeTicket $ticket)
    {
        if ($ticket->employee_id !== auth('employee')->id()) {
            return redirect()->route('employee.tickets.index')->with('error', 'You are not authorized to access this ticket.');
        }
//
//        $ticket->load('messages.admin', 'messages.employee');
//        return view('employee.tickets.chat', compact('ticket'));
        $ticket->load([
            'adminRelation',
            'employeeRelation',
            'attendanceRequest',
            'leaveRequest'
        ]);
        $messages = $ticket->messages()
            ->oldest()
            ->get();

        return view('employee.tickets.chat', compact('ticket', 'messages'));
    }
    public function store(Request $request, EmployeeTicket $ticket)
    {

        $request->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|max:2048',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json(['message' => 'Message or attachment required.'], 400);
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ticket_attachments', 'public');
        }

        TicketMessage::create([
            'ticket_id'       => $ticket->id,
            'sender_type'     => 'employee',
            'sender_id'       => auth('employee')->id(),
            'message'         => $request->message,
            'attachment_path' => $path,
        ]);

        return response()->json(['message' => 'Message sent successfully.'], 200);
    }

    public function fetchMessages(EmployeeTicket $ticket)
    {
        if ($ticket->employee_id !== auth('employee')->id()) {
            return redirect()->route('employee.tickets.index')->with('error', 'You are not authorized to access this ticket.');
        }

//        $ticket->load([
//            'adminRelation',
//            'employeeRelation',
//        ]);

        $messages = $ticket->messages()
            ->oldest()
            ->get();

        return view('employee.tickets.partials.messages', compact('messages'))->render();
    }
}
