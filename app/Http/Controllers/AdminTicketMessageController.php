<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTicket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class AdminTicketMessageController extends Controller
{

    public function index(EmployeeTicket $ticket)
    {
        $ticket->load([
            'adminRelation',
            'employeeRelation',
            'attendanceRequest',
            'leaveRequest'
        ]);
        $messages = $ticket->messages()
            ->oldest()
            ->get();
        return view('admin.tickets.chat', compact('ticket', 'messages'));
    }
    public function store(Request $request, EmployeeTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'attachment' => 'nullable|file|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ticket_attachments', 'public');
        }

        TicketMessage::create([
            'ticket_id'       => $ticket->id,
            'sender_type'     => 'admin',
            'sender_id'       => auth('admin')->id(), // 👈 correct guard lagao
            'message'         => $request->message,
            'attachment_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Reply sent successfully.');
    }

    public function fetchMessages(EmployeeTicket $ticket)
    {
        $ticket->load([
            'adminRelation',
            'employeeRelation',
        ]);

        $messages = $ticket->messages()
            ->oldest()
            ->get();

        return view('admin.tickets.partials.messages', compact('messages'))->render();
    }

}
