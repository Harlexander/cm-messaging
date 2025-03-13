import { useEffect, useState } from 'react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { ArrowUpDown } from 'lucide-react';
import moment from 'moment';
import axios from 'axios';
import { debounce } from 'lodash';

interface User {
    id: number;
    full_name: string;
    email: string;
    kingschat_id: string;
    kingschat_handle: string;
    designation: string;
    zone: string;
    country: string;
    joined_date: string;
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        designations: string[];
        zones: string[];
        countries: string[];
    };
}

export default function Users({ users: initialUsers, filters }: Props) {
    const [users, setUsers] = useState(initialUsers);
    const [loading, setLoading] = useState(false);
    const [search, setSearch] = useState('');
    const [sortField, setSortField] = useState('full_name');
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');
    const [selectedFilters, setSelectedFilters] = useState({
        designation: '',
        zone: '',
        country: '',
    });

    const fetchUsers = async (params = {}) => {
        setLoading(true);
        try {
            const response = await axios.get('/messaging/users/search', {
                params: {
                    search,
                    sort_field: sortField,
                    sort_direction: sortDirection,
                    ...selectedFilters,
                    ...params,
                }
            });
            setUsers(response.data);
        } catch (error) {
            console.error('Failed to fetch users:', error);
        }
        setLoading(false);
    };

    const debouncedFetch = debounce(fetchUsers, 300);

    useEffect(() => {
        debouncedFetch();
    }, [search, sortField, sortDirection, selectedFilters]);

    const handleSort = (field: string) => {
        const direction = field === sortField && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
    };

    const handleFilterChange = (field: string, value: string) => {
        setSelectedFilters(prev => ({
            ...prev,
            [field]: value === 'all' ? '' : value
        }));
    };

    const handlePageChange = (page: number) => {
        fetchUsers({ page });
    };

    return (
        <AppSidebarLayout>
            <div className="p-6">
                <h1 className="text-2xl font-bold mb-6">Users</h1>

                <div className="flex flex-col gap-4 md:flex-row md:items-center mb-6">
                    <Input
                        placeholder="Search users..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="max-w-sm"
                    />
                    <div className="flex gap-4">
                        <Select
                            value={selectedFilters.designation}
                            onValueChange={(value) => handleFilterChange('designation', value)}
                        >
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Designation" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Designations</SelectItem>
                                {filters.designations.map((designation) => (
                                    designation && (
                                        <SelectItem key={designation} value={designation}>
                                            {designation}
                                        </SelectItem>
                                    )
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={selectedFilters.zone}
                            onValueChange={(value) => handleFilterChange('zone', value)}
                        >
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Zone" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Zones</SelectItem>
                                {filters.zones.map((zone) => (
                                    zone && (
                                        <SelectItem key={zone} value={zone}>
                                            {zone}
                                        </SelectItem>
                                    )
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={selectedFilters.country}
                            onValueChange={(value) => handleFilterChange('country', value)}
                        >
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Country" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Countries</SelectItem>
                                {filters.countries.map((country) => (
                                    country && (
                                        <SelectItem key={country} value={country}>
                                            {country}
                                        </SelectItem>
                                    )
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <Card>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        onClick={() => handleSort('full_name')}
                                    >
                                        Name
                                        <ArrowUpDown className="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        onClick={() => handleSort('email')}
                                    >
                                        Email
                                        <ArrowUpDown className="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        onClick={() => handleSort('kingschat_handle')}
                                    >
                                        KingsChat
                                        <ArrowUpDown className="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>Designation</TableHead>
                                <TableHead>Zone</TableHead>
                                <TableHead>Country</TableHead>
                                <TableHead>
                                    <Button
                                        variant="ghost"
                                        onClick={() => handleSort('joined_date')}
                                    >
                                        Joined
                                        <ArrowUpDown className="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {loading ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={7}
                                        className="h-24 text-center"
                                    >
                                        Loading...
                                    </TableCell>
                                </TableRow>
                            ) : users.data.length ? (
                                users.data.map((user) => (
                                    <TableRow key={user.id}>
                                        <TableCell className="font-medium">{user.full_name}</TableCell>
                                        <TableCell>{user.email}</TableCell>
                                        <TableCell>{user.kingschat_handle}</TableCell>
                                        <TableCell>{user.designation}</TableCell>
                                        <TableCell>{user.zone}</TableCell>
                                        <TableCell>{user.country}</TableCell>
                                        <TableCell>{moment(user.joined_date).format('MMM D, YYYY')}</TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell
                                        colSpan={7}
                                        className="h-24 text-center"
                                    >
                                        No users found.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </Card>

                <div className="flex items-center justify-between space-x-2 py-4">
                    <div className="text-sm text-muted-foreground">
                        Showing {users.data.length} of {users.total} users
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(users.current_page - 1)}
                            disabled={users.current_page === 1}
                        >
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(users.current_page + 1)}
                            disabled={users.current_page === users.last_page}
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 