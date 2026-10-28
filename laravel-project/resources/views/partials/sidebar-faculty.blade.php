<li><a href="{{ route('faculty.dashboard') }}" class="{{ request()->routeIs('faculty.dashboard') ? 'active' : '' }}"><span><i class="fa-solid fa-house"></i> Dashboard</span></a></li>
<li><a href="{{ route('faculty.referrals.index') }}" class="{{ request()->routeIs('faculty.referrals.*') ? 'active' : '' }}"><span><i class="fa-solid fa-share-from-square"></i> Student Referral</span></a></li>
<li><a href="{{ route('faculty.appointments.create') }}" class="{{ request()->routeIs('faculty.appointments.*') ? 'active' : '' }}"><span><i class="fa-solid fa-calendar-plus"></i> Book Appointment</span></a></li>
<li><a href="{{ route('faculty.support-group.index') }}" class="{{ request()->routeIs('faculty.support-group.*') ? 'active' : '' }}"><span><i class="fa-solid fa-people-group"></i> Support Group</span></a></li>
<li><a href="{{ route('faculty.meetings') }}" class="{{ request()->routeIs('faculty.meetings') ? 'active' : '' }}"><span><i class="fa-solid fa-clock-rotate-left"></i> Meetings</span></a></li>
<li><a href="{{ route('faculty.notifications.index') }}" class="{{ request()->routeIs('faculty.notifications.*') ? 'active' : '' }}"><span><i class="fa-solid fa-bell"></i> Notifications</span></a></li>
<li><a href="{{ route('faculty.policy') }}" class="{{ request()->routeIs('faculty.policy*') ? 'active' : '' }}"><span><i class="fa-solid fa-file-lines"></i> Policy</span></a></li>
