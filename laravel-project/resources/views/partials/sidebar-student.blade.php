<li><a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}"><span><i class="fa-solid fa-house"></i> Dashboard</span></a></li>
<li><a href="{{ route('student.appointments.create') }}" class="{{ request()->routeIs('student.appointments.*') ? 'active' : '' }}"><span><i class="fa-solid fa-calendar-plus"></i> Book Appointment</span></a></li>
<li><a href="{{ route('student.support-group.index') }}" class="{{ request()->routeIs('student.support-group.*') ? 'active' : '' }}"><span><i class="fa-solid fa-people-group"></i> Support Group</span></a></li>
<li><a href="{{ route('student.meetings') }}" class="{{ request()->routeIs('student.meetings') ? 'active' : '' }}"><span><i class="fa-solid fa-clock-rotate-left"></i> Meetings</span></a></li>
<li><a href="{{ route('student.notifications.index') }}" class="{{ request()->routeIs('student.notifications.*') ? 'active' : '' }}"><span><i class="fa-solid fa-bell"></i> Notifications</span></a></li>
<li><a href="{{ route('student.policy') }}" class="{{ request()->routeIs('student.policy*') ? 'active' : '' }}"><span><i class="fa-solid fa-file-lines"></i> Policy</span></a></li>
