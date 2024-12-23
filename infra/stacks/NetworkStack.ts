import { Construct } from "constructs";
import { TerraformStack } from "cdktf";
import { SecurityGroupRule } from "@cdktf/provider-aws/lib/security-group-rule";
import { Vpc } from "@cdktf/provider-aws/lib/vpc";
import { Subnet } from "@cdktf/provider-aws/lib/subnet";
import { SecurityGroup } from "@cdktf/provider-aws/lib/security-group";
import { InternetGateway } from "@cdktf/provider-aws/lib/internet-gateway";
import { RouteTable } from "@cdktf/provider-aws/lib/route-table";
import { Route } from "@cdktf/provider-aws/lib/route";
import { RouteTableAssociation } from "@cdktf/provider-aws/lib/route-table-association";
import { NatGateway } from "@cdktf/provider-aws/lib/nat-gateway";
import { Eip } from "@cdktf/provider-aws/lib/eip";
import { AwsProvider } from "@cdktf/provider-aws/lib/provider";

interface NetworkStackProps {
    region: string;
}

export class NetworkStack extends TerraformStack {
    public readonly vpc: Vpc;
    public readonly publicSubnet: Subnet;
    public readonly privateSubnet: Subnet;
    public readonly databaseSecurityGroup: SecurityGroup;
    public readonly apiSecurityGroup: SecurityGroup;
    public readonly workerSecurityGroup: SecurityGroup;

    constructor(scope: Construct, id: string, { region }: NetworkStackProps) {
        super(scope, id);
        
        new AwsProvider(this, "AWS", {
            region,
        });

        const availabilityZone = "us-west-2a";

        // Create VPC
        this.vpc = new Vpc(this, "moooood-vpc", {
            cidrBlock: "10.0.0.0/24",
            tags: { Name: "moooood-vpc" },
        });

        // Create Subnets
        this.publicSubnet = new Subnet(this, "moooood-public-subnet", {
            vpcId: this.vpc.id,
            cidrBlock: "10.0.1.0/24",
            availabilityZone: availabilityZone,
            tags: { Name: "moooood-public-subnet" },
        });

        this.privateSubnet = new Subnet(this, "moooood-private-subnet", {
            vpcId: this.vpc.id,
            cidrBlock: "10.0.2.0/24",
            availabilityZone: availabilityZone,
            tags: { Name: "moooood-private-subnet" },
        });

        // Create Internet Gateway and Route Table
        const internetGateway = new InternetGateway(this, "moooood-igw", {
            vpcId: this.vpc.id,
            tags: { Name: "moooood-igw" },
        });

        const publicRouteTable = new RouteTable(this, "moooood-public-route-table", {
            vpcId: this.vpc.id,
            tags: { Name: "moooood-public-route-table" },
        });

        new Route(this, "moooood-public-route", {
            routeTableId: publicRouteTable.id,
            destinationCidrBlock: "0.0.0.0/0",
            gatewayId: internetGateway.id,
        });

        new RouteTableAssociation(this, "moooood-public-subnet-association", {
            routeTableId: publicRouteTable.id,
            subnetId: this.publicSubnet.id,
        });

        // Create NAT Gateway and Private Route Table
        const eip = new Eip(this, "moooood-eip", { tags: { Name: "moooood-eip" } });

        const natGateway = new NatGateway(this, "moooood-nat-gateway", {
            allocationId: eip.id,
            subnetId: this.publicSubnet.id,
            tags: { Name: "moooood-nat-gateway" },
        });

        const privateRouteTable = new RouteTable(this, "moooood-private-route-table", {
            vpcId: this.vpc.id,
            tags: { Name: "moooood-private-route-table" },
        });

        new Route(this, "moooood-private-route", {
            routeTableId: privateRouteTable.id,
            destinationCidrBlock: "0.0.0.0/0",
            natGatewayId: natGateway.id,
        });

        new RouteTableAssociation(this, "moooood-private-subnet-association", {
            routeTableId: privateRouteTable.id,
            subnetId: this.privateSubnet.id,
        });

        // Security Groups
        this.databaseSecurityGroup = new SecurityGroup(this, "moooood-db-sg", {
            vpcId: this.vpc.id,
            egress: [
                {
                    protocol: "-1",
                    fromPort: 0,
                    toPort: 0,
                    cidrBlocks: ["0.0.0.0/0"],
                },
            ],
            tags: { Name: "moooood-db-sg" },
        });

        this.apiSecurityGroup = new SecurityGroup(this, "moooood-public-api-sg", {
            vpcId: this.vpc.id,
            ingress: [
                {
                    protocol: "tcp",
                    fromPort: 80,
                    toPort: 80,
                    cidrBlocks: ["0.0.0.0/0"],
                },
            ],
            tags: { Name: "moooood-public-api-sg" },
        });

        this.workerSecurityGroup = new SecurityGroup(this, "moooood-worker-sg", {
            vpcId: this.vpc.id,
            ingress: [
                {
                    protocol: "tcp",
                    fromPort: 5432,
                    toPort: 5432,
                    securityGroups: [this.databaseSecurityGroup.id],
                },
            ],
            tags: { Name: "moooood-worker-sg" },
        });
        
        // Allow Public API Security Group to Access Database
        new SecurityGroupRule(this, "moooood-db-ingress-api", {
            type: "ingress",
            fromPort: 5432,
            toPort: 5432,
            protocol: "tcp",
            securityGroupId: this.databaseSecurityGroup.id,
            sourceSecurityGroupId: this.apiSecurityGroup.id,
        });
        
        // Allow Worker Security Group to Access Database
        new SecurityGroupRule(this, "moooood-db-ingress-worker", {
            type: "ingress",
            fromPort: 5432,
            toPort: 5432,
            protocol: "tcp",
            securityGroupId: this.databaseSecurityGroup.id,
            sourceSecurityGroupId: this.workerSecurityGroup.id,
        });
    }
}

export default NetworkStack;
